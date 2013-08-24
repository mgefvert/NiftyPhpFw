<?

// Run this from the command line ("php update_i18n.php") to scan all
// php and phtml files for language text and append into app/i18n.csv.

$strings = array();
$i18n    = array();

function load_i18n()
{
    global $i18n;

    if (($fp = fopen('../app/i18n.csv', 'r')) == false)
        die('No CSV');

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

function save_i18n(array $add)
{
    $fn = '../app/i18n.csv';

    $text = file_get_contents($fn);
    $text .= "\r\n\r\n" . implode("\r\n", $add);
    file_put_contents($fn, $text);
}

function parseFile($file)
{
    global $strings;

    echo $file, "\r\n";
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

load_i18n();
parseFolder('../app');
echo "\r\n";

$add = array();
foreach($strings as $s)
    if (!isset($i18n[$s]))
        $add[$s] = 1;
$add = array_keys($add);

if (!empty($add))
{
    sort($add);
    save_i18n($add);
    echo " - " . count($add) . " new string(s) found.\r\n";
}
else
    echo "No new strings found.\r\n";
