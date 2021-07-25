<?php

namespace App\CommonMark\Inline\Parser;


use App\CommonMark\CommonMarkConfig;
use App\Entity\EntityHasNameInterface;
use App\Entity\Version;
use App\Helpers\InternalLinkResolver;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Mention\Mention;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\CommonMark\Util\RegexHelper;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use Psr\Log\LoggerInterface;

/**
 * Extends the stock link parser to include support for internal links.
 *
 * Links should be in the format `[optional label]{category:slug}`.  If no label is specified (i.e.
 * `[]{category:slug}`), the label will be taken from the referenced entity's name.
 *
 * Because this parser operates just as the default CloseBracketParser, much of the code is the same.
 */
class CloseBracketInternalLinkParser implements InlineParserInterface, EnvironmentAwareInterface
{
    private const BRACKET_OPEN = '{';
    private const BRACKET_CLOSE = '}';

    private EnvironmentInterface $environment;
    /**
     * Current entity being worked with, guaranteed to have a getName() method.
     */
    private ?EntityHasNameInterface $currentEntity;

    /**
     * CloseBracketInternalLinkParser constructor.
     *
     * @param LoggerInterface $logger
     * @param InternalLinkResolver $linkResolver
     * @param Version $defaultVersion
     */
    public function __construct(
        private LoggerInterface $logger,
        private InternalLinkResolver $linkResolver,
        private Version $defaultVersion,
    ) {
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string(']');
    }

    /**
     * @inheritDoc
     */
    public function parse(InlineParserContext $inlineContext): bool
    {
        // Look through stack of delimiters for a [ or !
        $opener = $inlineContext->getDelimiterStack()->searchByCharacter(['[', '!']);
        if ($opener === null) {
            return false;
        }

        if (!$opener->isActive()) {
            // no matched opener; remove from emphasis stack
            $inlineContext->getDelimiterStack()->removeDelimiter($opener);

            return false;
        }

        $cursor = $inlineContext->getCursor();

        $previousState = $cursor->saveState();
        $cursor->advance();

        // Check to see if we have a link/image
        // Inline link?
        if ($result = $this->tryParseInlineLinkAndTitle($cursor)) {
            $link = $result;
        } else {
            // No match
            $inlineContext->getDelimiterStack()->removeDelimiter($opener); // Remove this opener from stack
            $cursor->restoreState($previousState);

            return false;
        }

        $isImage = ($opener->getChar() === '!');

        $inline = $this->createInline($link['url'], $link['title'], $isImage, $reference ?? null);

        $opener->getInlineNode()->replaceWith($inline);
        while (($label = $inline->next()) !== null) {
            $inline->appendChild($label);
        }
        // Add link text if needed
        if (empty($inline->children())) {
            // The link has no text.
            if ($this->currentEntity) {
                $inline->appendChild(new Text($this->currentEntity->getName()));
            } else {
                $this->logger->error(sprintf('Internal link to "%s" has no text.', $link['url']));
                $inline->appendChild(new Text('NO LINK TEXT'));
            }
        }
        while (($label = $inline->next()) !== null) {
            // Is there a Mention contained within this link?
            // CommonMark does not allow nested links, so we'll restore the original text.
            if ($label instanceof Mention) {
                $label->replaceWith($replacement = new Text($label->getPrefix().$label->getIdentifier()));
                $label = $replacement;
            }

            $inline->appendChild($label);
        }

        // Process delimiters such as emphasis inside link/image
        $delimiterStack = $inlineContext->getDelimiterStack();
        $stackBottom = $opener->getPrevious();
        $delimiterStack->processDelimiters($stackBottom, $this->environment->getDelimiterProcessors());
        $delimiterStack->removeAll($stackBottom);

        // processEmphasis will remove this and later delimiters.
        // Now, for a link, we also remove earlier link openers (no links in links)
        if (!$isImage) {
            $inlineContext->getDelimiterStack()->removeEarlierMatches('[');
        }

        return true;
    }

    private function tryParseInlineLinkAndTitle(Cursor $cursor): ?array
    {
        if ($cursor->getCharacter() === self::BRACKET_OPEN) {
            return $this->tryParseInternalLink($cursor);
        }

        return null;
    }

    /**
     * @param Cursor $cursor
     *
     * @return array|bool
     */
    private function tryParseInternalLink(Cursor $cursor): bool|array
    {
        $previousState = $cursor->saveState();

        // Parse URL
        $cursor->advance();
        $cursor->advanceToNextNonSpaceOrNewline();
        $dest = $this->parseInternalDestination($cursor);
        if ($dest === null) {
            $cursor->restoreState($previousState);

            return false;
        }
        $cursor->advance();

        // Don't support title text here.
        return ['url' => $dest, 'title' => null];
    }

    /**
     * @param Cursor $cursor
     *
     * @return string|null
     */
    private function parseInternalDestination(Cursor $cursor): ?string
    {
        // Get the text between brackets pointing to the destination.
        $oldState = $cursor->saveState();
        $openParens = 0;
        while (($c = $cursor->getCharacter()) !== null) {
            if ($c === '\\' && RegexHelper::isEscapable($cursor->peek())) {
                $cursor->advanceBy(2);
            } elseif ($c === self::BRACKET_OPEN) {
                $cursor->advance();
                $openParens++;
            } elseif ($c === self::BRACKET_CLOSE) {
                if ($openParens < 1) {
                    break;
                }

                $cursor->advance();
                $openParens--;
            } elseif (preg_match(RegexHelper::REGEX_WHITESPACE_CHAR, $c)) {
                break;
            } else {
                $cursor->advance();
            }
        }
        $newPos = $cursor->getPosition();
        $cursor->restoreState($oldState);
        $cursor->advanceBy($newPos - $cursor->getPosition());

        // The destination reference (e.g. "mechanic:hp")
        $ref = $cursor->getPreviousText();
        $refParts = $this->getRefParts($ref);
        if ($refParts === null) {
            return null;
        }

        $uri = $this->getUri($refParts['category'], $refParts['slug']);
        if ($uri === null) {
            // Help with finding bad links in the data.
            $this->logger->error(sprintf('Could not find destination for internal link: "%s".', $ref));
        }

        return $uri;
    }

    /**
     * @param string $ref
     *
     * @return array|null
     */
    private function getRefParts(string $ref): ?array
    {
        $badChars = ':'.self::BRACKET_OPEN.self::BRACKET_CLOSE;
        if (!preg_match("/^(?P<category>[^${badChars}]+):(?P<slug>[^${badChars}]*)$/", $ref, $matches)) {
            return null;
        }

        return $matches;
    }

    /**
     * Get the URI for the link.
     *
     * @param string $category
     * @param string $slug
     *
     * @return string|null
     */
    private function getUri(string $category, string $slug): ?string
    {
        $version = $this->resolveVersion();
        try {
            $this->currentEntity = $this->linkResolver->getEntityForLink($category, $slug, $version);
        } catch (\ValueError) {
            $this->currentEntity = null;
        }

        return $this->linkResolver->getLocation($category, $slug, $version);
    }

    /**
     * Use the current version if set, otherwise the default version
     *
     * @return Version
     */
    private function resolveVersion(): Version
    {
        $envConfig = $this->environment->getConfiguration();
        $version = $envConfig->exists(CommonMarkConfig::CURRENT_VERSION)
            ? $envConfig->get(CommonMarkConfig::CURRENT_VERSION)
            : $this->defaultVersion;
        if ($version instanceof LazyLoadingInterface && $version instanceof ValueHolderInterface) {
            $version->initializeProxy();
            $version = $version->getWrappedValueHolderValue();
        }

        return $version;
    }

    /**
     * @param string $url
     * @param string|null $title
     * @param bool $isImage
     *
     * @return AbstractWebResource
     */
    private function createInline(string $url, ?string $title, bool $isImage): AbstractWebResource
    {
        if ($isImage) {
            return new Image($url, null, $title);
        }

        return new Link($url, null, $title);
    }

    public function setEnvironment(EnvironmentInterface $environment): void
    {
        $this->environment = $environment;
    }

}
