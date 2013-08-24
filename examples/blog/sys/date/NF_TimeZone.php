<?php

/**
 * Helper function for timezones - handles basically two timezones, UTC and
 * local time.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_TimeZone
{
    protected static $utc;
    protected static $local;

    /**
     * Check to see if a time zone is the local time zone.
     *
     * @param DateTimeZone $tz
     * @return bool
     */
    public static function isLocal(DateTimeZone $tz)
    {
        return $tz->getName() == date_default_timezone_get();
    }

    /**
     * Check to see if a time zone is the UTC time zone.
     *
     * @param DateTimeZone $tz
     * @return bool
     */
    public static function isUtc(DateTimeZone $tz)
    {
        return $tz->getName() == 'UTC';
    }

    /**
     * Returns a cached instance of the local DateTimeZone
     *
     * @return DateTimeZone
     */
    public static function local()
    {
        if (self::$local !== null && self::$local->getName() != date_default_timezone_get())
            self::$local = null;

        if (self::$local === null)
            self::$local = new DateTimeZone(date_default_timezone_get());

        return self::$local;

    }

    /**
     * Returns a cached instance of the UTC DateTimeZone
     *
     * @return DateTimeZone
     */
    public static function utc()
    {
        if (self::$utc === null)
            self::$utc = new DateTimeZone('UTC');

        return self::$utc;
    }

    /**
     * Sets the local timezone.
     *
     * @param string $timezone Timezone identifier, e.g. "Europe/Stockholm"
     */
    public static function setLocal($timezone)
    {
        date_default_timezone_set($timezone);
        self::$local = null;
    }
}
