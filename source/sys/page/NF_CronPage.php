<?php

/**
 * Cron page class
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_CronPage extends NF_Page
{
    protected $jobs = array();

    protected function init()
    {
        parent::init();
        NF::response()->reset();
    }

    private function printException($job, Exception $e)
    {
        $jobText = $job ? 'while running job ' . get_class($job) : '';

        $class    = get_class($e);
        $message  = $e->getMessage();
        $line     = $e->getLine();
        $file     = $e->getFile();
        $now      = NF_DateTime::now();

        $msg = <<<EOF
$class occured in $file:$line at $now $jobText
 >> $message

EOF;
        echo $msg;
    }

    private function sendEmailResults($text)
    {
        $email_to   = NF::config()->main->system_email_to;
        $email_from = NF::config()->main->system_email_from ?: $email_to;

        if (!$email_to)
            return;

        $mail = new NF_SendMail();
        $mail->setFrom($email_from);
        $mail->setSubject('Cron job result');
        $mail->addTo($email_to);
        $mail->setText($text);
        $mail->send();
    }

    private function runJobs()
    {
        // Maximum ten minutes
        set_time_limit(10 * 60);

        $last = NF_CronJob::getLastRun();
        foreach($this->jobs as $job)
        {
            try
            {
                $job->check($last);
            }
            catch(Exception $e)
            {
                error_log('CRON: ' . get_class($e) . ' in ' . get_class($job) . ': ' . $e->getMessage());
                $this->printException($job, $e);
            }
        }

        NF_CronJob::updateLastRun();
    }

    public function executeRun()
    {
        ob_start();
        try
        {
            $this->runJobs();
        }
        catch(Exception $e)
        {
            error_log('CRON: ' . get_class($e) . ': ' . $e->getMessage());
            $this->printException(null, $e);
        }

        $text = trim(ob_get_contents());
        if ($text)
            $this->sendEmailResults($text);

        ob_end_clean();
    }
}
