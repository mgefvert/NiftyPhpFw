<?php

/**
 * Class that handles translations with data stored in a CSV file.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_TranslateCSV
{
    private $data = null;
    private $locales, $titles;

    public function __construct()
    {
        $this->load();
        if (!isset($_SESSION['__locale']))
            $this->setBrowserLocale();
    }

    public static function processMatchString($text)
    {
        $text = str_replace("\t", ' ', $text);
        $text = str_replace("\r", ' ', $text);
        $text = str_replace("\n", ' ', $text);

        while(strpos($text, '  ') !== false)
            $text = str_replace('  ', ' ', $text);

        return $text;
    }

    public function load()
    {
        $this->data = array();
        $this->locales = array();
        $this->titles  = array();

        $fn = NF_Path::$app . 'i18n.csv';

        if (!file_exists($fn))
            return false;

        $x = NF::cache()->get('__sys_i18n', filemtime($fn));
        if (!empty($x))
        {
            $this->data = $x['data'];
            $this->titles = $x['titles'];
            $this->locales = $x['locales'];
            return true;
        }

        if (($fp = fopen($fn, 'r')) == false)
            return false;

        $this->locales = fgetcsv($fp, 0, ';');
        foreach($this->locales as &$ref_s)
            $ref_s = strtolower($ref_s);
        $this->titles = fgetcsv($fp, 0, ';');

        while ($row = fgetcsv($fp, 0, ';'))
        {
            if (substr($row[0], 0, 1) == '#')
                continue;
            for($i=1; $i<count($row); $i++)
                $this->data[$i][$row[0]] = $row[$i];
        }

        fclose($fp);

        NF::cache()->set('__sys_i18n', array(
            'data'    => $this->data,
            'titles'  => $this->titles,
            'locales' => $this->locales
        ));
    }

    protected function findLocaleIndex($localeName)
    {
        $localeName = explode('-', str_replace('_', '-', strtolower($localeName)));
        $locales = array_flip($this->locales);

        for ($i=count($localeName); $i>=1; $i--)
        {
            $x = implode('-', array_slice($localeName, 0, $i));
            if (isset($locales[$x]))
                return $locales[$x];
        }

        return null;
    }

    public function getLocalePrefix()
    {
        return $this->locales[NF::session()->__locale];
    }

    public function getLocaleName()
    {
        return $this->titles[NF::session()->__locale];
    }

    public function resetLocale()
    {
        NF::session()->delete('__locale');
    }

    public function setLocale($locale)
    {
        $l = $this->findLocaleIndex($locale);
        NF::session()->__locale = $l ? $l : 0;
        return $l;
    }

    public function setBrowserLocale()
    {
        NF::session()->__locale = 0;

        $acceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            ? explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])
            : null;

        if (empty($acceptLanguage))
            return;

        foreach ($acceptLanguage as $l)
        {
            $l = strtolower(array_shift(explode(';', $l)));
            if (($n = $this->findLocaleIndex($l)) !== null)
            {
                NF::session()->__locale = $n;
                return;
            }
        }
    }

    public function translate($str, $localeName = null)
    {
        $l = $localeName ? $this->findLocaleIndex($localeName) : NF::session()->__locale;

        if (empty($this->data) || !$l)
            return $str;

        $str = self::processMatchString($str);

        return isset($this->data[$l][$str])
            ? $this->data[$l][$str]
            : $str;
    }
}
