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
if (substr($_SERVER["REQUEST_URI"], -11) == "readcfg.php") {
    header("Location:./");
};
// read config (defensive parsing: config.php formats vary)
function mikhmon_cfg_value($raw, $delimiter) {
    if (!is_string($raw)) return "";
    $parts = explode($delimiter, $raw, 2);
    return isset($parts[1]) ? $parts[1] : "";
}

function mikhmon_find_in_array($arr, $needle) {
    if (!is_array($arr)) return "";
    foreach ($arr as $v) {
        if (is_string($v) && strpos($v, $needle) !== false) return $v;
    }
    return "";
}

// If a session key is missing (or config is incomplete), avoid redirect loops and still render admin pages.
$__mikhmon_has_session_cfg = isset($session) && $session !== "" && isset($data) && is_array($data) && isset($data[$session]) && is_array($data[$session]);

if ($__mikhmon_has_session_cfg) {
    $iphost = mikhmon_cfg_value(isset($data[$session][1]) ? $data[$session][1] : "", "!");
    $userhost = mikhmon_cfg_value(isset($data[$session][2]) ? $data[$session][2] : "", "@|@");
    $passwdhost = mikhmon_cfg_value(isset($data[$session][3]) ? $data[$session][3] : "", "#|#");
    $hotspotname = mikhmon_cfg_value(isset($data[$session][4]) ? $data[$session][4] : "", "%");
    $dnsname = mikhmon_cfg_value(isset($data[$session][5]) ? $data[$session][5] : "", "^");
    $currency = mikhmon_cfg_value(isset($data[$session][6]) ? $data[$session][6] : "", "&");
    $areload = mikhmon_cfg_value(isset($data[$session][7]) ? $data[$session][7] : "", "*");
    $iface = mikhmon_cfg_value(isset($data[$session][8]) ? $data[$session][8] : "", "(");
    $infolp = mikhmon_cfg_value(isset($data[$session][9]) ? $data[$session][9] : "", ")");
    $idleto = mikhmon_cfg_value(isset($data[$session][10]) ? $data[$session][10] : "", "=");
    $sesname = $session;
    $livereport = mikhmon_cfg_value(isset($data[$session][11]) ? $data[$session][11] : "", "@!@");
    if ($currency === "") $currency = "Rp";
    if ($areload === "") $areload = "10";
    if ($iface === "") $iface = "1";
    if ($idleto === "") $idleto = "10";
    if ($livereport === "") $livereport = "disable";
} else {
    $iphost = "";
    $userhost = "";
    $passwdhost = "";
    $hotspotname = "";
    $dnsname = "";
    $currency = "Rp";
    $areload = "10";
    $iface = "1";
    $infolp = "";
    $idleto = "10";
    $sesname = isset($session) ? $session : "";
    $livereport = "disable";
}

// admin creds: don't depend on numeric indices
$mikhmonUserRaw = mikhmon_find_in_array(isset($data['mikhmon']) ? $data['mikhmon'] : array(), "<|<");
$mikhmonPassRaw = mikhmon_find_in_array(isset($data['mikhmon']) ? $data['mikhmon'] : array(), ">|>");
$useradm = mikhmon_cfg_value($mikhmonUserRaw, "<|<");
$passadm = mikhmon_cfg_value($mikhmonPassRaw, ">|>");

$cekindo['indo'] = array(
    'RP', 'Rp', 'rp', 'IDR', 'idr', 'RP.', 'Rp.', 'rp.', 'IDR.', 'idr.',
);


