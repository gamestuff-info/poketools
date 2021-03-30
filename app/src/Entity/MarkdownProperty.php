<?php


namespace App\Entity;

use Attribute;

/**
 * Denote a property that contains Markdown text
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MarkdownProperty
{
}
