<?php

namespace App\Support;

final class Colors
{
    public const HEX_REGEX = '/^#[0-9a-fA-F]{6}$/';

    public static function isHex(?string $value): bool
    {
        return is_string($value) && preg_match(self::HEX_REGEX, $value) === 1;
    }

    /**
     * يختار لون نص (أبيض/داكن) مقروء فوق لون الخلفية المعطى (WCAG relative luminance).
     */
    public static function onColor(string $hex): string
    {
        if (! self::isHex($hex)) {
            return '#ffffff';
        }

        [$r, $g, $b] = array_map(function (string $channel): float {
            $c = hexdec($channel) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, [substr($hex, 1, 2), substr($hex, 3, 2), substr($hex, 5, 2)]);

        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        return $luminance > 0.4 ? '#1c1917' : '#ffffff';
    }
}
