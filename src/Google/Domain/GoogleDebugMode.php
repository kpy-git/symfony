<?php

namespace App\Google\Domain;

class GoogleDebugMode
{
    public static function on(): bool
    {
        return $_ENV['GOOGLE_DEBUG_MODE'] === 'true';
    }

    public static function off(): bool
    {
        return $_ENV['GOOGLE_DEBUG_MODE'] !== 'true';
    }
}
