<?php

class NF_Color
{
    public $red;
    public $green;
    public $blue;
    public $alpha = 0xFF;

    public function __construct($r, $g, $b, $a = 0xFF)
    {
        $this->alpha = $a & 0xFF;
        $this->red   = $r & 0xFF;
        $this->green = $g & 0xFF;
        $this->blue  = $b & 0xFF;
    }

    public static function fromHex($color)
    {
        if (substr($color, 0, 1) == '#')
            $color = substr($color, 1);

        return self::fromInteger((int)hex2bin($color));
    }

    public static function fromInteger($value)
    {
        return new NF_Color($value >> 16, $value >> 8, $value, $value >> 24);
    }

    public function getHex()
    {
        return '#' . sprintf('%02x%02x%02x', $this->red, $this->green, $this->blue);
    }

    public function getInteger()
    {
        return
            ($this->alpha & 0xFF) << 24 |
            ($this->red   & 0xFF) << 16 |
            ($this->green & 0xFF) << 8 |
            ($this->blue  & 0xFF);
    }

    public function mix(NF_Color $color, $percent)
    {
        $pct = $percent / 100;
        $inv = 1 - $pct;

        return new NF_Color(
            $inv*$this->red   + $pct*$color->red,
            $inv*$this->green + $pct*$color->green,
            $inv*$this->blue  + $pct*$color->blue,
            $inv*$this->alpha + $pct*$color->alpha
        );
    }

    public function darken($percent)
    {
        return $this->mix(new NF_Color(0, 0, 0, $this->alpha), $percent);
    }

    public function lighten($percent)
    {
        return $this->mix(new NF_Color(255, 255, 255, $this->alpha), $percent);
    }
}
