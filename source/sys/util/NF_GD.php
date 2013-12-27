<?php

define('COLOR_BLACK',  0x000000);
define('COLOR_RED',    0xff0000);
define('COLOR_GREEN',  0x00ff00);
define('COLOR_BLUE',   0x0000ff);
define('COLOR_YELLOW', 0xffff00);
define('COLOR_PURPLE', 0xff00ff);
define('COLOR_CYAN',   0x00ffff);
define('COLOR_WHITE',  0xffffff);

/**
 * Encapsulates certain GD library function calls.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_GD
{
    /**
     *  The resource created by the GD function calls.
     */
    public $data = null;

    /**
     *  Constructor
     *
     *  @param resource $data Optional image data to encapsulate
     */
    public function __construct($data = null)
    {
        if (is_resource($data))
            $this->data = $data;
        else if ($data != null)
            $this->data = imagecreatefromstring($data);
    }

    /**
     *  Destructor
     */
    public function __destruct()
    {
        if ($this->data != null)
            imagedestroy($this->data);
    }

    /**
     *  Create a new image in memory.
     *
     *  @param int $width  Width of the new image
     *  @param int $height Height of the new image
     *
     *  @return bool
     */
    function create($width, $height)
    {
        $this->data = imagecreatetruecolor($width, $height);
        return $this->data != false;
    }

    /**
     *  Create an image from a string.
     *
     *  @param string $str The string containing the data.
     *
     *  @return bool
     */
    public function load($str)
    {
        $this->data = imagecreatefromstring($str);
        return $this->data != false;
    }

    /**
     *  Load a jpeg file into memory.
     *
     *  @param string $filename The physical filename to load.
     *
     *  @return bool
     */
    public function loadJPEG($filename)
    {
        $this->data = imagecreatefromjpeg($filename);
        return $this->data != false;
    }

    /**
     *  Load a png file into memory.
     *
     *  @param string $filename The physical filename to load.
     *
     *  @return bool
     */
    public function loadPNG($filename)
    {
        $this->data = imagecreatefrompng($filename);
        return $this->data != false;
    }

    /**
     *  Save a jpeg image to disk.
     *
     *  @param string $filename The physical filename to save the image as.
     *  @param int    $quality  Optional quality setting, normally 80
     *
     *  @return bool
     */
    function saveJPEG($filename, $quality = 80)
    {
        return imagejpeg($this->data, $filename, $quality);
    }

    /**
     *  Save a png image to disk.
     *
     *  @param string $filename The physical filename to save the image as.
     *
     *  @return bool
     */
    public function savePNG($filename)
    {
        return imagepng($this->data, $filename);
    }

    /**
     *  Send the jpeg image to the user
     *
     *  @return bool
     */
    public function outputJPEG()
    {
        return imagejpeg($this->data);
    }

    /**
     *  Send the png image to the user
     *
     *  @return bool
     */
    public function outputPNG()
    {
        return imagepng($this->data);
    }

    /**
     *  Return the jpeg image as a string
     *
     *  @return str
     */
    public function returnJPEG($quality = 90)
    {
        ob_start();
        imagejpeg($this->data, null, $quality);
        $img = ob_get_contents();
        ob_end_clean();

        return $img;
    }

    /**
     *  Return the png image as a string
     *
     *  @return str
     */
    public function returnPNG()
    {
        ob_start();
        imagepng($this->data);
        $img = ob_get_contents();
        ob_end_clean();

        return $img;
    }

    /**
     *  Retrieve the height of the image.
     *
     *  @return int
     */
    public function height()
    {
        return imagesy($this->data);
    }

    /**
     *  Retrieve the width of the image.
     *
     *  @return int
     */
    public function width()
    {
        return imagesx($this->data);
    }

    /**
     *  Change the size of the image by resampling. Usually destroys transparency.
     *
     *  @param int $x New width
     *  @param int $y New height
     *
     *  @return bool
     */
    public function resample($x, $y)
    {
        $data2 = imagecreatetruecolor($x, $y);

        $x = imagecopyresampled($data2, $this->data,
                0, 0, 0, 0,
                $x, $y, $this->width(), $this->height());

        if ($x == true) {
            imagedestroy($this->data);
            $this->data = $data2;
        }

        return $x;
    }

    /**
     *  Scale the image as resample() does, but preserving the relative dimension.
     *
     *  @param int $maxX New maximum width
     *  @param int $maxY New maximum height
     *
     *  @return bool
     */
    public function scale($maxX, $maxY)
    {
        $x = $this->width();
        $y = $this->height();

        $grow = max(ceil($maxX/$x), ceil($maxY/$y));

        $x = $x * $grow;
        $y = $y * $grow;

        if ($y > $maxY) {
            $x = $x * $maxY / $y;
            $y = $maxY;
        }

        if ($x > $maxX) {
            $y = $y * $maxX / $x;
            $x = $maxX;
        }

        return $this->resample(round($x), round($y));
    }

    /**
     * Make a thumbnail of size dimension x dimension. Will ensure that the
     * picture is properly cropped and centered
     *
     * @param int $dimX
     */
    public function thumbnail($dimX, $dimY)
    {
        $x = $this->width();
        $y = $this->height();

        if ($x > $y)
        {
            $x *= $dimY/$y;
            $y = $dimY;
        }
        else
        {
            $y *= $dimX/$x;
            $x = $dimX;
        }

        $this->resample(round($x), round($y));

        if ($x > $dimX)
        {
            $trim = floor(($x - $dimX)/2);
            $this->crop($trim, 0, $trim, 0);
        }
        else
        {
            $trim = floor(($y - $dimY)/2);
            $this->crop(0, $trim, 0, $trim);
        }

        if ($this->height() > $dimY || $this->width() > $dimX)
            $this->crop(0, 0, $this->width() - $dimX, $this->height() - $dimY);
    }

    /**
     *  Duplicate the image
     *
     *  @return NF_GD Copy of image
     */
    public function copy()
    {
        $res = new NF_GD();

        $res->create($this->width(), $this->height());
        imagecopy($res->data, $this->data,
            0, 0, 0, 0,
            $this->width(), $this->height());

        return $res;
    }

    /**
     *  Flip the image vertically
     *
     *  @return void
     */
    public function flip()
    {
        $h = $this->height();
        $w = $this->width();

        $data2 = imagecreatetruecolor($w, $h);

        for ($y=0; $y<$h; $y++)
            imagecopy($data2, $this->data, 0, $y, 0, $h-$y-1, $w, 1);

        imagedestroy($this->data);
        $this->data = $data2;
    }

    /**
     *  Mirror the image horisontally
     *
     *  @return void
     */
    public function mirror()
    {
        $h = $this->height();
        $w = $this->width();

        $data2 = imagecreatetruecolor($w, $h);

        for ($x=0; $x<$w; $x++)
            imagecopy($data2, $this->data, $x, 0, $w-$x-1, 0, 1, $h);

        imagedestroy($this->data);
        $this->data = $data2;
    }

    /**
     *  Crop an image
     *
     *  @param int $left   Pixels to cut off on the left
     *  @param int $top    Pixels to cut off on the top
     *  @param int $right  Pixels to cut off on the right
     *  @param int $bottom Pixels to cut off on the bottom
     *
     *  @return void
     */
    public function crop($left, $top, $right, $bottom)
    {
        $w = $this->width() - $left - $right;
        $h = $this->height() - $top - $bottom;

        $data2 = imagecreatetruecolor($w, $h);
        imagefilledrectangle($data2, 0, 0, imagesx($data2)-1, imagesy($data2)-1, COLOR_WHITE);
        imagecopy($data2, $this->data, 0, 0, $left, $top, $w, $h);

        imagedestroy($this->data);
        $this->data = $data2;
    }

    /**
     *  Stitch together two images
     *
     *  @param NF_GD $image1     First image
     *  @param NF_GD $image2     Second image
     *  @param bool  $vert       Add vertical (below) or horisontal (right)
     *  @param int   $background Background for filled-in blank areas
     *  @param int   $offset     Second image offset
     *
     *  @static
     *  @return NF_GD New GD image
     */
    static function stitch($image1, $image2, $vert, $background = COLOR_WHITE, $offset = 0)
    {
        if ($vert) {
            // Vertical stitching
            $height = $image1->height() + $image2->height();
            $width  = max($image1->width(), $image2->width());

            $res = new NF_GD();
            $res->create($width, $height);

            imagefilledrectangle($res->data, 0, 0, $width-1, $height-1, COLOR_WHITE);
            imagecopy($res->data, $image1->data, 0, 0, 0, 0, $image1->width(), $image1->height());
            imagecopy($res->data, $image2->data, $offset, $image1->height(), 0, 0, $image2->width(), $image2->height());

            return $res;
        } else {
            // Horisontal stitching
            $height = max($image1->height(), $image2->height());
            $width  = $image1->width() + $image2->width();

            $res = new NF_GD();
            $res->create($width, $height);

            imagefilledrectangle($res->data, 0, 0, $width-1, $height-1, COLOR_WHITE);
            imagecopy($res->data, $image1->data, 0, 0, 0, 0, $image1->width(), $image1->height());
            imagecopy($res->data, $image2->data, $image1->width(), $offset, 0, 0, $image2->width(), $image2->height());

            return $res;
        }
    }
}
