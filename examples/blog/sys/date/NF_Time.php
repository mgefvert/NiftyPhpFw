<?php

/**
 * NF_Time handles time in the same way that NF_Date handles dates, storing
 * time values as the number of seconds since midnight.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Time
{
    protected $time;

    /**
     * Create a new time value, either set to null, or another NF_Time, or
     * a string on the format hh:nn or hh:nn:ss.
     *
     * @param mixed $value
     * @throws NF_Exception
     */
    public function __construct($value = null)
    {
        if ($value instanceof NF_Time)
            $this->time = $value->time;
        else if (is_string($value) && strpos($value, ':') !== false)
        {
            $x = explode(':', $value);
            while (count($x) < 3)
                $x[] = 0;

            $this->time = (int)($x[0] * 3600 + $x[1] * 60 + $x[2]);
        }
        else if ($value === null || $value === '')
            $this->time = null;
        else
            throw new NF_Exception("Unknown time value: " . $value);
    }

    /**
     * Return a new NF_Time value initialized to the number of given hours.
     * Value can be either a string or a number.
     *
     * @param mixed $value
     * @return NF_Time
     */
    public static function fromHours($value)
    {
        if (is_string($value))
        {
            $lc = localeconv();
            $value = str_replace('.', $lc['decimal_point'], $value);
            $value = str_replace(',', $lc['decimal_point'], $value);
        }

        $result = new NF_Time();
        $result->time = (int)(floatval($value) * 3600);
        return $result;
    }

    /**
     * Return a new NF_Time value initialized to the number of given seconds.
     *
     * @param int $value
     * @return NF_Time
     */
    public static function fromSeconds($value)
    {
        $result = new NF_Time();
        $result->time = (int)$value;
        return $result;
    }

    /**
     * Return a new NF_Time value initialized to the time given.
     *
     * @param int $h  Hours
     * @param int $m  Minutes
     * @param int $s  Seconds
     * @return NF_Time
     */
    public static function fromTime($h = 0, $m = 0, $s = 0)
    {
        $result = new NF_Time();
        $result->time = (int)($h * 3600 + $m * 60 + $s);
        return $result;
    }

    /**
     * Create a string representation of the time value.
     *
     * @return string
     */
    public function  __toString()
    {
        if ($this->time === null)
            return '';

        $x = $this->getTime();
        return sprintf('%02d:%02d:%02d', $x['hour'], $x['minute'], $x['second']);
    }

    /**
     *  ToValue conversion - convert this to a string or null value
     *
     *  @return mixed
     */
    public function toValue()
    {
        return $this->time === null ? null : $this->__toString();
    }

    /**
     * Return an array with the keys second, minute and hour to represent
     * the time value.
     *
     * @return array
     */
    public function getTime()
    {
        if ($this->time === null)
            return null;

        $t = round($this->time);

        $result = array();
        $result['second'] = $t % 60;
        $t = floor($t / 60);
        $result['minute'] = $t % 60;
        $result['hour'] = floor($t / 60);

        return $result;
    }

    /**
     * Return the time value as the number of whole hours.
     *
     * @return int
     */
    public function getHours()
    {
        return $this->time === null ? null : (int)floor($this->time / 3600);
    }

    /**
     * Return the minutes part of the time value (0-59).
     *
     * @return int
     */
    public function getMinutes()
    {
        return $this->time === null ? null : floor($this->time / 60) % 60;
    }

    /**
     * Return the seconds part of the time value (0-59).
     *
     * @return int
     */
    public function getSeconds()
    {
        return $this->time === null ? null : $this->time % 60;
    }

    /**
     * Return the time value expressed in hours, e.g. 03:30:00 will be
     * returned as 3.5.
     *
     * @return double
     */
    public function asHours()
    {
        return $this->time === null ? null : $this->time / 3600;
    }

    /**
     * return the time value expressed in seconds, e.g. 00:30:01 will be
     * returned as 1801 seconds.
     *
     * @return int
     */
    public function asSeconds()
    {
        return $this->time === null ? null : $this->time;
    }

    /**
     * Add a certain number of hours, minutes and seconds to the time value.
     *
     * @param int $h
     * @param int $m
     * @param int $s
     */
    public function add($h = 0, $m = 0, $s = 0)
    {
        $this->time += $h * 3600 + $m * 60 + $s;
    }

    /**
     * Format the time value according to regular php date() formats, most
     * options unavailable, of course, as we only deal with time.
     *
     * @param string $fmt
     * @return string
     */
    public function format($fmt)
    {
        $result = '';
        $t = $this->getTime();

        for($i=0; $i<strlen($fmt); $i++) {
            switch ($fmt{$i})
            {
                case 'G':
                    $result .= $t['hour'];
                    break;
                case 'H':
                    $result .= str_pad($t['hour'], 2, '0', STR_PAD_LEFT);
                    break;
                case 'i':
                    $result .= str_pad($t['minute'], 2, '0', STR_PAD_LEFT);
                    break;
                case 's':
                    $result .= str_pad($t['second'], 2, '0', STR_PAD_LEFT);
                    break;
                case '\\':
                    $i++;
                    $result .= $fmt{$i};
                    break;
                default:
                    $result .= $fmt{$i};
            }
        }

        return $result;
    }
}
