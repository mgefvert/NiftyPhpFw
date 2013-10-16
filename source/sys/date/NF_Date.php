<?php

/**
 * The NF_Date class wraps a PHP DateTime and provides some more efficient
 * accessor methods, only handling dates (not time) and being completely agnostic
 * to time zones.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Date
{
    /**
     * A DateTime value (or null) that we wrap, always enforced to UTC
     * so we don't end up with weird things because of daylight savings
     * time or similar.
     *
     * @var DateTime
     */
    public $dateTime = null;

    /**
     * Construct a new instance of NF_Date
     *
     * @param mixed $value       Value can be a NF_Date (copy), a NF_DateTime,
     *                           or a PHP DateTime object. It can also be a string,
     *                           in which case it is simply passed to the DateTime
     *                           constructor, or if it's NULL, will initialize a
     *                           null NF_Date.
     */
    public function __construct($value = null)
    {
        if ($value instanceof DateTime)
            $value = new NF_DateTime($value);

        if ($value instanceof NF_Date)
            $this->dateTime = clone $value->dateTime;
        else if ($value instanceof NF_DateTime)
            $this->setDate($value->getYear(), $value->getMonth(), $value->getDay());
        else if ($value === null)
            $this->dateTime = null;
        else
            $this->dateTime = new DateTime($value, NF_TimeZone::utc());

        if ($this->dateTime !== null)
            $this->dateTime->setTime(0, 0, 0);
    }

    /**
     * Internal methods that checks if the wrapped DateTime is null and
     * throws an exception if it is, preventing further modification.
     */
    protected function checkNullDate()
    {
        if (!$this->dateTime)
            throw new NF_EDateError('Cannot modify a NULL date');
    }


    /**
     * Compare two dates and return negative if smaller, positive is larger, or
     * zero if they're equal.
     *
     *  @param mixed $dt1 First date
     *  @param mixed $dt2 Second date
     *  @return int
     */
    public static function compare($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_Date)) $dt1 = new NF_Date($dt1);
        if (!($dt2 instanceof NF_Date)) $dt2 = new NF_Date($dt2);

        return $dt1->getDays() - $dt2->getDays();
    }

    /**
     * Calculate the difference between two dates and return the difference
     * as a DateInterval.
     *
     * @param mixed $dt1   First NF_Date (or any other value that can be used to initialize a NF_Date)
     * @param mixed $dt2   Second NF_Date
     * @return DateInterval
     */
    public static function interval($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_Date)) $dt1 = new NF_Date($dt1);
        if (!($dt2 instanceof NF_Date)) $dt2 = new NF_Date($dt2);

        $dt1->checkNullDate();
        $dt2->checkNullDate();

        return $dt1->dateTime->diff($dt2->dateTime);
    }

    /**
     * Calculate the difference between two dates and return the difference
     * as the number of days between them
     *
     * @param mixed $dt1   First NF_Date (or any other value that can be used to initialize a NF_Date)
     * @param mixed $dt2   Second NF_Date
     * @return float
     */
    public static function daysBetween($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_Date)) $dt1 = new NF_Date($dt1);
        if (!($dt2 instanceof NF_Date)) $dt2 = new NF_Date($dt2);

        $dt1->checkNullDate();
        $dt2->checkNullDate();

        return $dt2->getDays() - $dt1->getDays();
    }

    /**
     * Set the date and time from an Excel-style (Delphi-style) float value,
     * where the integer part is the number of days since 1899-12-31 and the
     * fraction part is the time of day.
     *
     * @param float $datetime
     */
    public static function fromDays($datetime)
    {
        $date = floor($datetime);

        // Pure magic
        $l = $date + 68569 + 2415019;
        $n = floor((4 * $l) / 146097);
        $l = $l - floor((146097 * $n + 3) / 4);
        $i = floor((4000 * ($l + 1)) / 1461001);
        $l = $l - floor((1461 * $i) / 4) + 31;
        $j = floor((80 * $l) / 2447);
        $day = $l - floor((2447 * $j) / 80);
        $l = floor($j / 11);
        $month = $j + 2 - (12 * $l);
        $year = 100 * ($n - 49) + $i + $l;

        $result = new NF_Date();
        $result->setDate($year, $month, $day);

        return $result;
    }

    /**
     * Return the greater date from two given dates.
     *
     * @param mixed $dt1   First NF_Date (or any other value that can be used to initialize a NF_Date)
     * @param mixed $dt2   Second NF_Date
     * @return NF_Date
     */
    public static function max($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_Date)) $dt1 = new NF_Date($dt1);
        if (!($dt2 instanceof NF_Date)) $dt2 = new NF_Date($dt2);

        return self::compare($dt1, $dt2) > 0 ? $dt1 : $dt2;
    }

    /**
     * Return the lesser date from two given dates.
     *
     * @param mixed $dt1   First NF_Date (or any other value that can be used to initialize a NF_Date)
     * @param mixed $dt2   Second NF_Date
     * @return NF_Date
     */
    public static function min($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_Date)) $dt1 = new NF_Date($dt1);
        if (!($dt2 instanceof NF_Date)) $dt2 = new NF_Date($dt2);

        return self::compare($dt1, $dt2) <= 0 ? $dt1 : $dt2;
    }

    /**
     * Return a NF_Date object with the current date (time is zero).
     *
     * @return NF_Date
     */
    public static function today()
    {
        $result = new NF_Date();
        $result->setDate(idate('Y'), idate('m'), idate('d'));

        return $result;
    }

    /**
     *  ToString conversion - make a normal yyyy-mm-dd hh:nn:ss timestamp
     *
     *  @return string
     */
    public function __toString()
    {
        if ($this->isNull())
            return '';

        return $this->dateTime->format('Y-m-d');
    }

    /**
     *  ToString conversion - make a normal yyyy-mm-dd hh:nn:ss timestamp
     *
     *  @return string
     */
    public function toValue()
    {
        if ($this->isNull())
            return null;

        return $this->dateTime->format('Y-m-d');
    }

    /**
     * Add an interval to the datetime
     *
     * @param mixed $interval  Either a DateInterval, or a string to initialize a DateInterval
     */
    public function add($interval)
    {
        $this->checkNullDate();
        if (!($interval instanceof DateInterval))
            $interval = new DateInterval($interval);

        $this->dateTime->add($interval);
    }

    /**
     *  Reset the datetime to null
     *
     *  @return void
     */
    public function clear()
    {
        $this->dateTime = null;
    }

    /**
     *  Format date and time according to a format value. Uses the syntax
     *  used by date().
     *
     *  @param string $fmt See php date() function
     *  @return string
     */
    public function format($fmt)
    {
        if ($this->dateTime === null)
            return '';

        return $this->dateTime->format($fmt);
    }

    /**
     *  Decode the date/time into an array. The keys in the array are
     *  year, month, day, and wday.
     *
     *  @return array
     */
    public function getDate()
    {
        if ($this->dateTime === null)
            return null;

        $values = explode('|', $this->dateTime->format('Y|n|j|w'));

        $result['year']   = (int)$values[0];
        $result['month']  = (int)$values[1];
        $result['day']    = (int)$values[2];
        $result['wday']   = (int)$values[3];

        return $result;
    }

    /**
     * Return the day of the month.
     *
     * @return int
     */
    public function getDay()
    {
        return $this->dateTime ? (int)$this->dateTime->format('j') : null;
    }

    /**
     * Return the date and time expressed as an Excel-style (Delphi-style)
     * float value. The integer part is the number of days since 1899-12-31 and
     * the fraction part is the time of the day.
     *
     * @return float
     */
    public function getDays()
    {
        $daysPerMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if ($this->isLeapYear())
            $daysPerMonth[1]++;

        list($year, $month, $day) = array_values($this->getDate());

        for ($i=1; $i<$month; $i++)
            $day += $daysPerMonth[$i - 1];

        $year--;
        return (int)($year * 365 + floor($year / 4) - floor($year / 100) +
               floor($year / 400) + $day - 693594);
    }

    /**
     * Return the ISO weekday (1=Monday, 7=Sunday)
     *
     * @return int
     */
    public function getIsoWeekday()
    {
        return $this->dateTime ? (int)$this->dateTime->format('N') : null;
    }

    /**
     * Return the month.
     *
     * @return int
     */
    public function getMonth()
    {
        return $this->dateTime ? (int)$this->dateTime->format('n') : null;
    }

    /**
     * Get the ISO week number
     *
     * @return int
     */
    public function getWeek()
    {
        return $this->dateTime ? (int)$this->dateTime->format('W') : null;
    }

    /**
     * Return the day of the week (0=Sunday, 6=Saturday)
     *
     * @return int
     */
    public function getWeekday()
    {
        return $this->dateTime ? (int)$this->dateTime->format('w') : null;
    }

    /**
     * Get the current ISO week and the year to which it belongs
     *
     * @param int &$year  Variable that gets assigned the year
     * @param int &$week  Variable that gets assigned the week
     */
    public function getWeekYear(&$year, &$week)
    {
        $year = $this->dateTime ? (int)$this->dateTime->format('o') : null;
        $week = $this->dateTime ? (int)$this->dateTime->format('W') : null;
    }

    /**
     * Return the year.
     *
     * @return int
     */
    public function getYear()
    {
        return $this->dateTime ? (int)$this->dateTime->format('Y') : null;
    }

    /**
     * Test whether the date is null or not.
     *
     * @return bool
     */
    public function isNull()
    {
        return $this->dateTime === null;
    }

    /**
     * Is the day a weekend (Sat or Sun)?
     *
     * @return bool
     */
    public function isWeekend()
    {
        return $this->dateTime ? $this->getIsoWeekday() >= 6 : false;
    }

    /**
     * Is the year a leap year?
     *
     * @return int
     */
    public function isLeapYear()
    {
        return $this->dateTime ? $this->dateTime->format('L') == 1 : null;
    }

    /**
     * Set the date. If the date is null, the time will initialize to 00:00:00.
     *
     * @param int $year   Year
     * @param int $month  Month
     * @param int $day    Day
     */
    public function setDate($year, $month, $day)
    {
        $this->dateTime = new DateTime(null, NF_TimeZone::utc());
        $this->dateTime->setDate($year, $month, $day);
        $this->dateTime->setTime(0, 0, 0);
    }

    /**
     * Subtract an interval from the given datetime.
     *
     * @param mixed $interval  A DateInterval, or any string that can be used
     *                         to initialize a DateInterval.
     */
    public function sub($interval)
    {
        $this->checkNullDate();
        if (!($interval instanceof DateInterval))
            $interval = new DateInterval($interval);

        $this->dateTime->sub($interval);
    }
}
