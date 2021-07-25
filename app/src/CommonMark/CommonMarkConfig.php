<?php

namespace App\CommonMark;

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
}
