<?php

interface NF_CronJob_Aspect
{
    public function timeToRun(NF_DateTime $last);
}

class NF_CronJob_Weekly implements NF_CronJob_Aspect
{
    public function timeToRun(NF_DateTime $last)
    {
        // Time it so it always occurs on Sundays
        $now = NF_DateTime::now();
        $now_week = (int)(($now->date - 1) / 7);
        $last_week = (int)(($last->date - 1) / 7);

        return $last_week < $now_week;
    }
}

class NF_CronJob_Daily implements NF_CronJob_Aspect
{
    public function timeToRun(NF_DateTime $last)
    {
        $now = NF_DateTime::now();

        return $last->date < $now->date;
    }
}

class NF_CronJob_Hourly implements NF_CronJob_Aspect
{
    public function timeToRun(NF_DateTime $last)
    {
        $now = NF_DateTime::now();
        $now_hours = ($now->date * 24) + ($now->getHour());
        $last_hours = ($last->date * 24) + ($last->getHour());

        return $last_hours < $now_hours;
    }
}

class NF_CronJob_Continually implements NF_CronJob_Aspect
{
    protected $minuteInterval;

    public function __construct($minuteInterval = 5)
    {
        $this->minuteInterval = $minuteInterval;
    }

    public function timeToRun(NF_DateTime $last)
    {
        $secsAffinity = $this->minuteInterval * 60;

        $now  = (int)(NF_DateTime::now()->getTimestamp() / $secsAffinity);
        $last = (int)($last->getTimestamp() / $secsAffinity);

        return $now > $last;
    }
}

class NF_CronJob_Always implements NF_CronJob_Aspect
{
    public function timeToRun(NF_DateTime $last)
    {
        return true;
    }
}

/**
 * Provides a base class for cron jobs.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
abstract class NF_CronJob
{
    protected $aspect;

    public function __construct(NF_CronJob_Aspect $aspect)
    {
        $this->aspect = $aspect;
    }

    public function check(NF_DateTime $last)
    {
        if ($this->aspect->timeToRun($last))
            $this->execute();
    }

    public abstract function execute();

    public static function getLastRun()
    {
        $data = NF_Cache::get('__sys_cron');
        if ($data)
        {
            $data = unserialize($data);
            if ($data instanceof NF_DateTime)
                return $data;
        }

        return new NF_DateTime();
    }

    public static function updateLastRun()
    {
        NF_Cache::set('__sys_cron', serialize(NF_DateTime::now()));
    }
}
