<?php

namespace Tests\Unit;

use App\Support\Colors;
use App\Support\Slug;
use PHPUnit\Framework\TestCase;

class SupportTest extends TestCase
{
    public function test_slug_keeps_arabic_letters_and_normalises_separators(): void
    {
        $this->assertSame('قميص-قطني-أزرق', Slug::make('  قميص قطني   أزرق!! '));
        $this->assertSame('hello-world-2', Slug::make('Hello, World 2'));
        $this->assertSame('', Slug::make('!!!'));
    }

    public function test_hex_validation(): void
    {
        $this->assertTrue(Colors::isHex('#0f766e'));
        $this->assertFalse(Colors::isHex('0f766e'));
        $this->assertFalse(Colors::isHex('#fff'));
        $this->assertFalse(Colors::isHex('red;} body{display:none'));
        $this->assertFalse(Colors::isHex(null));
    }

    public function test_on_color_picks_readable_text(): void
    {
        $this->assertSame('#ffffff', Colors::onColor('#000000'));
        $this->assertSame('#1c1917', Colors::onColor('#ffffff'));
        $this->assertSame('#1c1917', Colors::onColor('#f59e0b'));
        $this->assertSame('#ffffff', Colors::onColor('#0f766e'));
    }
}
