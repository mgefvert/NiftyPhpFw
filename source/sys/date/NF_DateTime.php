<?php

/**
 * The NF_DateTime class wraps a PHP DateTime and provides some more efficient
 * accessor methods. It is fully compliant with timezone handling.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_DateTime
{
    /**
     * A DateTime value (or null) that we wrap.
     *
     * @var DateTime
     */
    public $dateTime = null;

    /**
     * Construct a new instance of NF_DateTime
     *
     * @param mixed $value       Value can be a NF_DateTime (copy), a DateTimeEx
     *                           or DateEx, or a PHP DateTime object. It can
     *                           also be a string, in which case it is simply
     *                           passed to the DateTime constructor, or if it's
     *                           NULL, will initialize a null NF_DateTime.
     * @param DateTimeZone $tz   Optional DateTimeZone
     */
    public function __construct($value = null, DateTimeZone $tz = null)
    {
        if ($tz == null)
            $tz = NF_TimeZone::local();

        if ($value instanceof NF_DateTime)
            $this->dateTime = clone $value->dateTime;
        else if ($value instanceof DateTime)
            $this->dateTime = $value;
        else if ($value instanceof NF_Date)
            $this->setDateTime($value->getYear(), $value->getMonth(), $value->getDay(), 0, 0, 0);
        else if ($value === null)
            $this->dateTime = null;
        else
            $this->dateTime = new DateTime($value, $tz);
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
     * @param mixed $dt1 First date
     * @param mixed $dt2 Second date
     * @return int
     */
    public static function compare($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_DateTime)) $dt1 = new NF_DateTime($dt1);
        if (!($dt2 instanceof NF_DateTime)) $dt2 = new NF_DateTime($dt2);

        return $dt1->getTimestamp() - $dt2->getTimestamp();
    }

    /**
     * Calculate the difference between two dates and return the difference
     * as a DateInterval.
     *
     * @param mixed $dt1   First NF_DateTime (or any other value that can be used to initialize a NF_DateTime)
     * @param mixed $dt2   Second NF_DateTime
     * @return DateInterval
     */
    public static function interval($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_DateTime)) $dt1 = new NF_DateTime($dt1);
        if (!($dt2 instanceof NF_DateTime)) $dt2 = new NF_DateTime($dt2);

        $dt1->checkNullDate();
        $dt2->checkNullDate();

        return $dt1->dateTime->diff($dt2->dateTime);
    }

    /**
     * Calculate the difference between two dates and return the difference
     * as the number of days between them
     *
     * @param mixed $dt1   First NF_DateTime (or any other value that can be used to initialize a NF_DateTime)
     * @param mixed $dt2   Second NF_DateTime
     * @return float
     */
    public static function daysBetween($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_DateTime)) $dt1 = new NF_DateTime($dt1);
        if (!($dt2 instanceof NF_DateTime)) $dt2 = new NF_DateTime($dt2);

        $dt1->checkNullDate();
        $dt2->checkNullDate();

        return ($dt2->getTimestamp() - $dt1->getTimestamp()) / 86400;
    }

    /**
     * Set the date and time from an Excel-style (Delphi-style) float value,
     * where the integer part is the number of days since 1899-12-31 and the
     * fraction part is the time of day.
     *
     * @param float $datetime
     */
    public static function fromDouble($datetime)
    {
        $date = floor($datetime);
        $time = $datetime - $date;

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

        $t = round($time * 86400);
        $second = $t % 60;
        $t = floor($t / 60);
        $minute = $t % 60;
        $hour = floor($t / 60);

        $result = new NF_DateTime();
        $result->setDateTime($year, $month, $day, $hour, $minute, $second);

        return $result;
    }

    /**
     * Create a new NF_DateTime from a timestamp value. Will handle timezones
     * correctly, which is not guaranteed if you initialize a NF_DateTime through
     * passing a "@timestamp" value in the constructor.
     *
     * @param int $ts Timestamp
     *
     * @static
     * @return NF_DateTime
     */
    public static function fromTimestamp($ts)
    {
        $dt = new NF_DateTime();
        $dt->setTimestamp($ts);
        return $dt;
    }

    /**
     * Return the greater date from two given dates.
     *
     * @param mixed $dt1   First NF_DateTime (or any other value that can be used to initialize a NF_DateTime)
     * @param mixed $dt2   Second NF_DateTime
     * @return NF_DateTime
     */
    public static function max($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_DateTime)) $dt1 = new NF_DateTime($dt1);
        if (!($dt2 instanceof NF_DateTime)) $dt2 = new NF_DateTime($dt2);

        return self::compare($dt1, $dt2) > 0 ? $dt1 : $dt2;
    }

    /**
     * Return the lesser date from two given dates.
     *
     * @param mixed $dt1   First NF_DateTime (or any other value that can be used to initialize a NF_DateTime)
     * @param mixed $dt2   Second NF_DateTime
     * @return NF_DateTime
     */
    public static function min($dt1, $dt2)
    {
        if (!($dt1 instanceof NF_DateTime)) $dt1 = new NF_DateTime($dt1);
        if (!($dt2 instanceof NF_DateTime)) $dt2 = new NF_DateTime($dt2);

        return self::compare($dt1, $dt2) <= 0 ? $dt1 : $dt2;
    }

    /**
     * Return a NF_DateTime object with the current date and time
     *
     * @return NF_DateTime
     */
    public static function now($tz = null)
    {
        return new NF_DateTime(new DateTime('now', $tz ?: NF_TimeZone::local()));
    }

    /**
     * Return a NF_DateTime object with the current date and time, set to UTC
     *
     * @return NF_DateTime
     */
    public static function utcNow()
    {
        return new NF_DateTime(new DateTime('now', NF_TimeZone::utc()));
    }

    /**
     * Return a NF_DateTime object with the current date (time is zero).
     *
     * @return NF_DateTime
     */
    public static function today()
    {
        $value = new DateTime('now');
        $value->setTime(0, 0, 0);

        return new NF_DateTime($value);
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

        return $this->dateTime->format('Y-m-d H:i:s');
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

        return $this->dateTime->format('Y-m-d H:i:s');
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
     *  year, month, day, hour, minute, second, and wday.
     *
     *  @return array
     */
    public function getDateTime()
    {
        if ($this->dateTime === null)
            return null;

        $values = explode('|', $this->dateTime->format('Y|n|j|H|i|s|w'));

        $result['year']   = (int)$values[0];
        $result['month']  = (int)$values[1];
        $result['day']    = (int)$values[2];
        $result['hour']   = (int)$values[3];
        $result['minute'] = (int)$values[4];
        $result['second'] = (int)$values[5];
        $result['wday']   = (int)$values[6];

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
    public function getDouble()
    {
        if ($this->isNull())
            return null;

        $daysPerMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if ($this->isLeapYear())
            $daysPerMonth[1]++;

        list($year, $month, $day, $hour, $minute, $second) = array_values($this->getDateTime());

        for ($i=1; $i<$month; $i++)
            $day += $daysPerMonth[$i - 1];

        $year--;
        return $year * 365 + floor($year / 4) - floor($year / 100) +
               floor($year / 400) + $day - 693594 +
               ($hour * 3600 + $minute * 60 + $second) / 86400;
    }

    /**
     * Return the date and time expressed as an Excel-style (Delphi-style)
     * float value. The integer part is the number of days since 1899-12-31 and
     * the fraction part is the time of the day.
     *
     * @return int
     */
    public function getDoubleDate()
    {
        if ($this->isNull())
            return null;

        $daysPerMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if ($this->isLeapYear())
            $daysPerMonth[1]++;

        list($year, $month, $day) = array_values($this->getDateTime());

        for ($i=1; $i<$month; $i++)
            $day += $daysPerMonth[$i - 1];

        $year--;
        return (int)($year * 365 + floor($year / 4) - floor($year / 100) +
               floor($year / 400) + $day - 693594);
    }

    /**
     * Return the date and time expressed as an Excel-style (Delphi-style)
     * float value. The integer part is the number of days since 1899-12-31 and
     * the fraction part is the time of the day.
     *
     * @return float
     */
    public function getDoubleTime()
    {
        if ($this->isNull())
            return null;

        list(, , , $hour, $minute, $second) = array_values($this->getDateTime());

        return ($hour * 3600 + $minute * 60 + $second) / 86400;
    }

    /**
     * Return the hour.
     *
     * @return int
     */
    public function getHour()
    {
        return $this->dateTime ? (int)$this->dateTime->format('H') : null;
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
     * Return the minute.
     *
     * @return int
     */
    public function getMinute()
    {
        return $this->dateTime ? (int)$this->dateTime->format('i') : null;
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
     * Return the current timezone UTC offset in seconds.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->dateTime ? (int)$this->dateTime->getOffset() : null;
    }

    /**
     * Return the second.
     *
     * @return int
     */
    public function getSecond()
    {
        return $this->dateTime ? (int)$this->dateTime->format('s') : null;
    }

    /**
     * Get date/time as unix timestamp.
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->dateTime ? $this->dateTime->getTimestamp() : null;
    }

    /**
     * Get the current timezone.
     *
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        return $this->dateTime ? $this->dateTime->getTimezone() : null;
    }

    /**
     * Get the current timezone name
     *
     * @return string
     */
    public function getTimezoneName()
    {
        $tz = $this->getTimezone();
        return $tz ? $tz->getName() : null;
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
     * Determines if the particular date is today. Today's value can be
     * adjusted by a number of days to mean tomorrow, yesterday etc.
     *
     * @param int $offsetDays  Days to offset.
     * @return bool
     */
    public function isToday($offsetDays = 0)
    {
        $today = new DateTime();

        $offsetDays = (int)$offsetDays;
        if ($offsetDays > 0)
            $today->add(new DateInterval('P' . $offsetDays . 'D'));
        else if ($offsetDays < 0)
            $today->sub(new DateInterval('P' . abs($offsetDays) . 'D'));

        return $this->format('Yz') == $today->format('Yz');
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
        if (!$this->dateTime)
        {
            $this->dateTime = new DateTime();
            $this->dateTime->setDate($year, $month, $day);
            $this->dateTime->setTime(0, 0, 0);
        }
        else
            $this->dateTime->setDate($year, $month, $day);
    }

    /**
     * Set both date and time.
     *
     * @param int $year    Year
     * @param int $month   Month
     * @param int $day     Day
     * @param int $hour    Hour
     * @param int $minute  Minute
     * @param int $second  Second
     */
    public function setDateTime($year, $month, $day, $hour, $minute, $second)
    {
        if (!$this->dateTime)
            $this->dateTime = new DateTime();

        $this->dateTime->setDate($year, $month, $day);
        $this->dateTime->setTime($hour, $minute, $second);
    }

    /**
     * Set time to hour, minute, second. If the datetime is null, will initialize
     * the date part to today.
     *
     * @param int $hour    Hour
     * @param int $minute  Minute
     * @param int $second  Second
     */
    public function setTime($hour, $minute, $second)
    {
        if (!$this->dateTime)
            $this->dateTime = new DateTime();

        $this->dateTime->setTime($hour, $minute, $second);
    }

    /**
     * Set date/time to a unix timestamp
     *
     * @param int $ts  Timestamp
     */
    public function setTimestamp($ts)
    {
        if (!$this->dateTime)
            $this->dateTime = new DateTime();

        $this->dateTime->setTimestamp($ts);
    }

    /**
     * Set the timezone of the NF_DateTime.
     *
     * @param mixed $tz  Timezone, or any value that can be used to initialize
     *                   a DateTimeZone. If null or omitted, the default time
     *                   zone will be used.
     */
    public function setTimezone(DateTimeZone $tz)
    {
        $this->checkNullDate();
        $this->dateTime->setTimezone($tz);
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

    /**
     * Transform the datetime to local time and return it.
     *
     * @return NF_DateTime
     */
    public function toLocal()
    {
        if ($this->dateTime === null || NF_TimeZone::isLocal($this->dateTime->getTimezone()))
            return $this;

        $result = new NF_DateTime($this);
        $result->setTimezone(NF_TimeZone::local());
        return $result;
    }

    /**
     * Transform the datetime to UTC and return it,
     *
     * @return NF_DateTime
     */
    public function toUtc()
    {
        if ($this->dateTime === null || NF_TimeZone::isUtc($this->dateTime->getTimezone()))
            return $this;

        $result = new NF_DateTime($this);
        $result->setTimezone(NF_TimeZone::utc());
        return $result;
    }
}
