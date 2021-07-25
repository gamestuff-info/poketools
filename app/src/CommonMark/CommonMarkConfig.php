<?php

namespace App\CommonMark;

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Util\HtmlFilter;

/**
 * Hold config names
 *
 * @todo enum
 */
final class CommonMarkConfig
{
    public const CONFIG_NAMESPACE = 'poketools';

    public const UNQUALIFIED_CURRENT_VERSION = "current_version";
    public const CURRENT_VERSION = self::CONFIG_NAMESPACE.'/'.self::UNQUALIFIED_CURRENT_VERSION;

    public static function get(): array
    {
        return [
            'html_input' => HtmlFilter::ALLOW,
            'allow_unsafe_links' => false,
            'default_attributes' => [
                Table::class => [
                    'class' => ['table', 'table-sm'],
                ],
                BlockQuote::class => [
                    'class' => 'blockquote',
                ],
            ],
            self::CONFIG_NAMESPACE => [
                self::UNQUALIFIED_CURRENT_VERSION => null,
            ],
        ];
    }
}
