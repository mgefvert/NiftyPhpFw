<?php

class NF_TextTest extends PHPUnit_Framework_TestCase
{
    public function testFromUnicode()
    {
        $s = 'Hej där! ÅÄÖ!';
        $this->assertEquals(NF_Text::fromUnicode(utf8_encode($s)), $s);
    }

    public function testToUnicode()
    {
        $s = 'Hej där! ÅÄÖ!';
        $this->assertEquals(NF_Text::toUnicode($s), utf8_encode($s));
    }

    public function testCut()
    {
        $s = 'Hello! Nice hat!';

        $this->assertEquals(NF_Text::cut($s, ' '), 'Hello!');
        $this->assertEquals($s, 'Nice hat!');

        $this->assertEquals(NF_Text::cut($s, ' '), 'Nice');
        $this->assertEquals($s, 'hat!');

        $this->assertEquals(NF_Text::cut($s, ' '), 'hat!');
        $this->assertEquals($s, '');
    }

    public function testFromUnicodeArray()
    {
        $s[1] = 'Hej där! ÅÄÖ!';
        $s[2] = 'Hej där! Yug!';

        $unicode[1] = utf8_encode($s[1]);
        $unicode[2] = utf8_encode($s[2]);

        $this->assertNotEquals($s[1], $unicode[1]);
        $this->assertNotEquals($s[2], $unicode[2]);

        NF_Text::fromUnicodeArray($unicode);

        $this->assertEquals($s[1], $unicode[1]);
        $this->assertEquals($s[2], $unicode[2]);
    }

    public function testToUnicodeArray()
    {
        $s[1] = 'Hej där! ÅÄÖ!';
        $s[2] = 'Hej där! Yug!';

        $unicode[1] = utf8_encode($s[1]);
        $unicode[2] = utf8_encode($s[2]);

        $this->assertNotEquals($s[1], $unicode[1]);
        $this->assertNotEquals($s[2], $unicode[2]);

        NF_Text::toUnicodeArray($s);

        $this->assertEquals($s[1], $unicode[1]);
        $this->assertEquals($s[2], $unicode[2]);
    }

    public function testEllipsis()
    {
        $text = 'Hello, this is a great text.';
        $this->assertEquals($text, NF_Text::ellipsis($text));
        $this->assertEquals('Hello, this is a...', NF_Text::ellipsis($text, 20));

        $text = 'Hellothisisagreattext';
        $this->assertEquals($text, NF_Text::ellipsis($text));
        $this->assertEquals('Hellothisi...', NF_Text::ellipsis($text, 10));
    }

    public function testMinify()
    {
    }

    public function testSeparateTagsAndText()
    {
        $html = '<b>This <i>is</i> a test</b>';

        $tags = NF_Text::separateTagsAndText($html);

        $this->assertEquals(7, count($tags));

        $this->assertEquals('<b>',     $tags[0]);
        $this->assertEquals('This ',   $tags[1]);
        $this->assertEquals('<i>',     $tags[2]);
        $this->assertEquals('is',      $tags[3]);
        $this->assertEquals('</i>',    $tags[4]);
        $this->assertEquals(' a test', $tags[5]);
        $this->assertEquals('</b>',    $tags[6]);
    }

    public function testSuppress()
    {
        $this->assertEquals('ABCDEF ABCDEF', NF_Text::suppress('abc', 'ABCDEF ABCDEF'));
        $this->assertEquals('DEF DEF',       NF_Text::suppress('ABC', 'ABCDEF ABCDEF'));
        $this->assertEquals('ABCDEFABCDEF',  NF_Text::suppress(' ', 'ABC DEF ABC DEF'));
    }

    public function testSuppressL()
    {
        $this->assertEquals('ABCDEF ABCDEF', NF_Text::suppressL('abc', 'ABCDEF ABCDEF'));
        $this->assertEquals('DEF ABCDEF',    NF_Text::suppressL('ABC', 'ABCDEF ABCDEF'));
    }

    public function testSuppressR()
    {
        $this->assertEquals('ABCDEF ABCDEF', NF_Text::suppressR('def', 'ABCDEF ABCDEF'));
        $this->assertEquals('ABCDEF ABC',    NF_Text::suppressR('DEF', 'ABCDEF ABCDEF'));
    }
}
