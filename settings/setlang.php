<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// hide all error
error_reporting(0);

@include_once(__DIR__ . '/../include/ajax.php');

// check url
$url2 = explode("&setlang", $url)[0];

$getlang = $_GET['setlang'];

if (empty($getlang)) {

} else {
    if (!empty($isocodelang[$getlang])) {
        $gen = '<?php $langid="' . $getlang . '";?>';
        // Use absolute path and don't hard-fail on unwritable FS.
        $slang = __DIR__ . '/../include/lang.php';
        $handle = @fopen($slang, 'w');
        if ($handle !== false) {
            @fwrite($handle, $gen);
            @fclose($handle);
        }
        $_SESSION['lang'] = $getlang;

        $isAjax = function_exists('mikhmon_is_ajax') ? mikhmon_is_ajax() : false;
        if ($isAjax && function_exists('mikhmon_json')) {
            mikhmon_json(array(
                "ok" => true,
                "lang" => $getlang,
                "redirect" => $url2,
            ));
        }

        if (!headers_sent()) {
            header("Location: " . $url2);
            exit;
        }
        echo "<script>window.location='" . $url2 . "'</script>";
        
    } else {
        $isAjax = function_exists('mikhmon_is_ajax') ? mikhmon_is_ajax() : false;
        if ($isAjax && function_exists('mikhmon_json')) {
            mikhmon_json(array(
                "ok" => false,
                "error" => "lang_not_found",
                "redirect" => $url2,
            ), 400);
        }

        if (!headers_sent()) {
            header("Location: " . $url2);
            exit;
        }
        echo "<script>window.location='" . $url2 . "'</script>";
    }
}

?>