<?php

namespace App\CommonMark\Inline\Parser;


use App\Entity\EntityHasNameInterface;
use App\Entity\Version;
use App\Helpers\InternalLinkResolver;
use League\CommonMark\Cursor;
use League\CommonMark\EnvironmentAwareInterface;
use League\CommonMark\EnvironmentInterface;
use League\CommonMark\Inline\Element\AbstractWebResource;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Util\RegexHelper;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use Psr\Log\LoggerInterface;

/**
 * Extends the stock link parser to include support for internal links.
 *
 * Links should be in the format `[optional label]{category:slug}`.  If no label is specified (i.e.
 * `[]{category:slug}`), the label will be taken from the referenced entity's name.
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
        $link = $this->tryParseInlineLinkAndTitle($cursor);
        if (!$link) {
            // No match
            $cursor->restoreState($previousState);

            return false;
        }

        $isImage = ($opener->getChar() === '!');

        $inline = $this->createInline($link['url'], $link['title'], $isImage);

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

        // Process delimiters such as emphasis inside link/image
        $delimiterStack = $inlineContext->getDelimiterStack();
        $stackBottom = $opener->getPrevious();
        $delimiterStack->processDelimiters($stackBottom, $this->environment->getDelimiterProcessors());
        $delimiterStack->removeAll($stackBottom);

        return true;
    }

    private function tryParseInlineLinkAndTitle(Cursor $cursor): bool|array
    {
        if ($cursor->getCharacter() === self::BRACKET_OPEN) {
            return $this->tryParseInternalLink($cursor);
        }

        return false;
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
        $version = $this->environment->getConfig('currentVersion', $this->defaultVersion);
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

    /**
     * @inheritDoc
     */
    public function getCharacters(): array
    {
        return [']'];
    }

    /**
     * @inheritDoc
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }
}
