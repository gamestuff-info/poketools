<?php
/**
 * @file TableRenderer.php
 */

namespace App\CommonMark\Renderer;

use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableRenderer as ParentTableRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Add required styling to all tables
 */
class TableRenderer implements NodeRendererInterface
{
    /**
     * @var ParentTableRenderer
     */
    private $tableRenderer;

    /**
     * TableRenderer constructor.
     *
     * @param ParentTableRenderer $tableRenderer
     */
    public function __construct(ParentTableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    /**
     * @inheritDoc
     *
     * @param Table $node
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        /** @var HtmlElement $element */
        $element = $this->tableRenderer->render($node, $childRenderer);
        $element->setAttribute('class', 'table table-sm');

        return $element;
    }
}
