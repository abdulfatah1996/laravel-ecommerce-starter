<?php

namespace App\Support;

final class Slug
{
    /**
     * رابط صديق يدعم الحروف العربية (Unicode) بدل حذفها.
     */
    public static function make(?string $text): string
    {
        $text = mb_strtolower(trim((string) $text));
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? '';

        return trim($text, '-');
    }
}
