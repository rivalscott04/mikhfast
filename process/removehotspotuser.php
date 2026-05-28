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
include_once('./include/ajax.php');
if ($removehotspotusers != "") {
	$uids = explode("~", $removehotspotusers);

	$nuids = count($uids);

	for ($i = 0; $i < $nuids; $i++) {

		$getuname = $API->comm("/ip/hotspot/user/print", array(
			"?.id" => "$uids[$i]",
			".proplist" => "name",
		));

		$name = $getuname[0]['name'];

		$API->comm("/system/script/remove", array(
			"numbers" => "$name",
		));

		$API->comm("/system/scheduler/remove", array(
			"numbers" => "$name",
		));

		$API->comm("/ip/hotspot/user/remove", array(
			".id" => "$uids[$i]",
		));

	}

	if ($_SESSION['ubp'] != "") {
		$redirect = "./?hotspot=users&profile=" . $_SESSION['ubp'] . "&session=" . $session;
	} elseif ($_SESSION['ubc'] != "") {
		$redirect = "./?hotspot=users&comment=" . $_SESSION['ubc'] . "&session=" . $session;
	} else {
		$redirect = "./?hotspot=users&profile=all&session=" . $session;
	}
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";


} else {
	$getuname = $API->comm("/ip/hotspot/user/print", array(
		"?.id" => "$removehotspotuser",
		".proplist" => "name",
	));

	$name = $getuname[0]['name'];

	$API->comm("/system/script/remove", array(
		"numbers" => "$name",
	));

	$API->comm("/system/scheduler/remove", array(
		"numbers" => "$name",
	));

	$API->comm("/ip/hotspot/user/remove", array(
		".id" => "$removehotspotuser",
	));


	if ($_SESSION['ubp'] != "") {
		$redirect = "./?hotspot=users&profile=" . $_SESSION['ubp'] . "&session=" . $session;
	} elseif ($_SESSION['ubc'] != "") {
		$redirect = "./?hotspot=users&comment=" . $_SESSION['ubc'] . "&session=" . $session;
	} else {
		$redirect = "./?hotspot=users&profile=all&session=" . $session;
	}
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";
}
?>