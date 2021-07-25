<?php

namespace App\CommonMark\Handler;

use Ds\Map;
use League\CommonMark\Event\AbstractEvent;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Event\DocumentRenderedEvent;
use League\CommonMark\Input\MarkdownInput;
use League\CommonMark\Output\RenderedContent;
use League\CommonMark\Util\RegexHelper;

/**
 * Escape JSX tags for CommonMark so they are not double-escaped.
 */
class JsxEscaper
{
    /**
     * List of allowed JSX elements.
     */
    private const ALLOWED_JSX_ELEMENTS = [
        'FlaggedMoveTable',
        'MachinePokemonTable',
        'PokemonEvolvesWithItemTable',
        'MathJax',
    ];

    private Map $replacements;

    public function __construct()
    {
        $this->replacements = new Map();
    }


    public function __invoke(AbstractEvent $e)
    {
        match (get_class($e)) {
            DocumentPreParsedEvent::class => $this->handleDocumentPreParsed($e),
            DocumentRenderedEvent::class => $this->handleDocumentRendered($e),
        };
    }

    private function handleDocumentPreParsed(DocumentPreParsedEvent $e): void
    {
        // Replace allowed JSX tags with placeholders.
        $content = $e->getMarkdown()->getContent();
        $content = preg_replace_callback(
            '/<(?P<tag>'.RegexHelper::PARTIAL_TAGNAME.')'.RegexHelper::PARTIAL_ATTRIBUTE.'*\s*\/?>/ui',
            function (array $matches) {
                [0 => $match, 'tag' => $tag] = $matches;
                if (!in_array($tag, self::ALLOWED_JSX_ELEMENTS)) {
                    return $match;
                }
                $placeholder = '--JSX-TAG-'.count($this->replacements).'--';
                $this->replacements->put($placeholder, $match);

                return $placeholder;
            },
            $content
        );
        $e->replaceMarkdown(new MarkdownInput($content));
    }

    private function handleDocumentRendered(DocumentRenderedEvent $e): void
    {
        // Replace the placeholders with saved JSX tags.
        $content = $e->getOutput()->getContent();
        $content = str_replace(
            $this->replacements->keys()->toArray(),
            $this->replacements->values()->toArray(),
            $content
        );
        $e->replaceOutput(new RenderedContent($e->getOutput()->getDocument(), $content));
        $this->replacements->clear();
    }
}
