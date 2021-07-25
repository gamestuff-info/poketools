<?php
/**
 * @file PoketoolsTableExtension.php
 */

namespace App\CommonMark\Extension;


use App\CommonMark\Renderer\TableRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Table\Table;

/**
 * Extend the TableExtension to support special rendering
 */
class PoketoolsTableExtension implements ExtensionInterface
{

    /**
     * PoketoolsTableExtension constructor.
     *
     * @param TableRenderer $tableRenderer
     */
    public function __construct(
        private TableRenderer $tableRenderer
    ) {
    }

    /**
     * @inheritDoc
     */
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addRenderer(Table::class, $this->tableRenderer, 200);
    }
}
