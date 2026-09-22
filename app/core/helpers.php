<?php

use App\Core\Lang;

/**
 * Translate a key. Falls back to the key itself if not found.
 * Usage: __('nav.contacts')  or  __('flash.created')
 */
function __(string $key, array $replace = []): string
{
    return Lang::get($key, $replace);
}
