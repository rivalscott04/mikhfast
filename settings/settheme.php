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
session_start();
// hide all error
error_reporting(0);

// Ensure AJAX helpers available (safe if already included by parent).
@include_once(__DIR__ . '/../include/ajax.php');

// check url
$url2 = explode("&set-theme", $url)[0];

$gettheme = $_GET['set-theme'];
$mtheme = array(
    "dark",
    "light",
    "blue",
    "green",
    "pink",
    
);
$theme_color = array(
    "#3a4149",
    "#008BC9",
    "#008BC9",
    "#4dbd74",
    "#e83e8c",
);


$themenum = array_search($gettheme, $mtheme);

// guard for invalid search result (false => null index)
$getthemecolor = isset($theme_color[$themenum]) ? $theme_color[$themenum] : "";

if (empty($gettheme)) {  

} else {
    if (in_array($gettheme, $mtheme)) {
        $_SESSION['theme'] = $gettheme;
        $_SESSION['themecolor'] = $getthemecolor;

        // Persist default theme file if possible (optional).
        $gen = '<?php $theme="' . $gettheme . '"; $themecolor="'.$getthemecolor.'";?>';
        $stheme = __DIR__ . '/../include/theme.php';
        $handle = @fopen($stheme, 'w');
        if ($handle !== false) {
            @fwrite($handle, $gen);
            @fclose($handle);
        }

        $isAjax = function_exists('mikhmon_is_ajax') ? mikhmon_is_ajax() : false;
        if ($isAjax && function_exists('mikhmon_json')) {
            mikhmon_json(array(
                "ok" => true,
                "theme" => $gettheme,
                "themecolor" => $getthemecolor,
                "redirect" => $url2,
            ));
        }

        // Fast redirect (no intermediate "loading theme" page).
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
                "error" => "theme_not_found",
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