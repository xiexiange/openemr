<?php

/**
 * Global Functions Library
 *
 * This file centralizes all global function definitions to avoid
 * duplicate declarations across the codebase.
 *
 * Include this file using require_once(dirname(__FILE__) . '/../library/global_functions.php');
 * or require_once(__DIR__ . '/../library/global_functions.php');
 */

/**
 * Debug function. Can expand for longer trace or file info.
 */
function GetCallingScriptName()
{
    $e = new Exception();
    return $e->getTrace()[1]['file'];
}

/**
 * Global interface function to format text length using ellipses
 */
function strterm($string, $length)
{
    if (strlen((string) $string) >= ($length - 3)) {
        return substr((string) $string, 0, $length - 3) . "...";
    } else {
        return $string;
    }
}

/**
 * Helper function to generate an image URL that defeats browser/proxy caching when needed.
 */
function UrlIfImageExists($filename, $append = true)
{
    global $webserver_root, $web_root;
    $path = "sites/" . $_SESSION['site_id'] . "/images/$filename";
    // @ in next line because a missing file is not an error.
    if ($stat = @stat("$webserver_root/$path")) {
        if ($append) {
            return "$web_root/$path?v=" . $stat['mtime'];
        } else {
            return "$web_root/$path";
        }
    }
    return '';
}

/**
 * Get default language information
 */
function getDefaultLanguage(): array
{
    $sql = "SELECT * FROM lang_languages where lang_description = ?";
    $res = sqlStatement($sql, [$GLOBALS['language_default']]);
    $langs = [];

    while ($row = sqlFetchArray($res)) {
        $langs[] = $row;
    }

    $id = 1;
    $desc = "English";

    if (count($langs) == 1) {
        $id = $langs[0]["lang_id"];
        $desc = $langs[0]["lang_description"];
    }

    return ["id" => $id, "language" => $desc];
}

/**
 * DEPRECATED; just keeping this for backward compatibility.
 *
 * Decrypts the string
 * @param $value
 * @return bool|string
 */
function my_decrypt($data)
{
    // Remove the base64 encoding from our key
    $encryption_key = base64_decode((string) $GLOBALS['safe_key_database']);
    // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
    [$encrypted_data, $iv] = explode('::', base64_decode((string) $data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}