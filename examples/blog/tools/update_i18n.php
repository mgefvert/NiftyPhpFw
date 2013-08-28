<?

// Run this from the command line ("php update_i18n.php") to scan all
// php and phtml files for language text and append into app/i18n.csv.

$strings = array();
$i18n    = array();

function load_i18n($fn)
{
    global $i18n;

    if (($fp = fopen($fn, 'r')) == false)
    {
        echo "Can't open file $fn\r\n";
        return;
    }

    fgetcsv($fp, 0, ';');
    fgetcsv($fp, 0, ';');

    while ($row = fgetcsv($fp, 0, ';'))
    {
        if ($row == null || substr($row[0], 0, 1) == '#')
            continue;
        $i18n[$row[0]] = 1;
    }

    fclose($fp);
}

function save_i18n($fn, array $add)
{
    $text = file_get_contents($fn);
    $text .= "\r\n\r\n" . implode("\r\n", $add);
    file_put_contents($fn, $text);
}

function parseFile($file)
{
    global $strings;

    $text = file_get_contents($file);

    if (preg_match_all('/_t\([\'"](.*)[\'"]\)/U', $text, $matches))
        $strings = array_merge($strings, $matches[1]);
    if (preg_match_all('/\[@(.*)\]/U', $text, $matches))
        $strings = array_merge($strings, $matches[1]);
}

function parseFolder($folder)
{
    $dirs = glob($folder . '/*', GLOB_ONLYDIR);
    $files = array_merge(
        glob($folder . '/*.php'),
        glob($folder . '/*.phtml')
    );

    if (!empty($files))
        foreach($files as $f)
            parseFile($f);

    if (!empty($dirs))
        foreach($dirs as $f)
            parseFolder($f);
}

$paths = glob('../app*', GLOB_ONLYDIR);

foreach($paths as $path)
{
    load_i18n($path . '/i18n.csv');
    parseFolder($path);
    echo "\r\n";

    $add = array();
    foreach($strings as $s)
        if (!isset($i18n[$s]))
            $add[$s] = 1;
    $add = array_keys($add);

    if (!empty($add))
    {
        sort($add);
        save_i18n($path . '/i18n.csv', $add);
        echo $path . ": " . count($add) . " new string(s) found.\r\n";
    }
    else
        echo $path . ": No new strings found.\r\n";
}
