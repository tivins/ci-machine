<?php

/**
 * @file The purpose of this file is to log capabilities of the system.
 *
 * Because it needs to be strictly equals to the requested environment.
 * We will store the most useful data in an array, converted in JSON file.
 *
 * @todo OK. But, what if php_json is not enabled ?
 *
 */

/**
 * Suppress most IDE warning, because we don't know the current PHP version for the execution of this file.
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpComposerExtensionStubsInspection
 * @noinspection SpellCheckingInspection
 */

$data                  = [];
$data['containerTime'] = date('c');
$data['sql']           = tryConnect('database', 'db', 3306, 'admin', 'adminpass');
echo($data['php-version'] = "PHP " . PHP_VERSION), PHP_EOL;

tryFunction($data, 'json_encode');
tryFunction($data, 'curl_init');
tryFunction($data, 'iconv');
tryFunction($data, 'mb_strlen');
tryFunction($data, 'yaml_parse');
tryFunction($data, 'finfo_file');
tryFunction($data, 'token_get_all');
tryFunction($data, 'imagecreatetruecolor');
tryFunction($data, 'simplexml_load_string');
tryFunction($data, 'libxml_clear_errors');
tryFunction($data, 'mysqli_connect');
tryFunction($data, 'gettext');
tryFunction($data, 'gmp_abs');
tryFunction($data, 'gnupg_encrypt');
tryFunction($data, 'openssl_open');
tryFunction($data, 'imap_open');
tryFunction($data, 'Locale', 'class');
tryFunction($data, 'Imagick', 'class');
tryFunction($data, 'PDO', 'class');
tryFunction($data, 'xdebug', 'extension');


file_put_contents('/box/test-info.json', json_encode($data));

function tryFunction(array &$data, $name, $type = 'function')
{
    $func = $type . '_exists';
    if ($type == 'extension') {
        $func = 'extension_loaded';
    }
    $data["$type/$name"] = $func($name);
    echo ($func($name) ? '✓' : ' ') . '] ' . $name . ' (' . $type . ')' . "\n";
}

function tryConnect($dbname, $host, $port = 3306, $user = 'root', $password = '')
{
    static $counter = 0;
    $counter++;

    try
    {
        $pdo = new PDO("mysql:dbname=$dbname;host=$host;port=$port", $user, $password);
        return ($pdo->query("show databases")->fetchAll(PDO::FETCH_COLUMN));
    }
    catch (Exception $ex)
    {
        echo "FAIL #$counter : ", $ex->getMessage(), PHP_EOL;
        return $ex->getMessage();
    }
}