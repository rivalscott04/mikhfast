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

// remove ip binding
if ($removeipbinding != "") {
	include_once('./lib/router/RouterService.php');
	$router = new RouterService($API);
	$router->removeHotspotIpBinding($removeipbinding);

	$getqueue = $API->comm("/queue/simple/print", array(
		"?name" => "$macbinding",
	));

	$squeue = $getqueue[0]['.id'];

	$API->comm("/queue/simple/remove", array(
		".id" => "$squeue",
	));

	$getvalid = $API->comm("/system/scheduler/print", array(
		"?name" => "$macbinding",
	));

	$svalid = $getvalid[0]['.id'];

	$API->comm("/system/scheduler/remove", array(
		".id" => "$svalid",
	));

	$getarp = $API->comm("/ip/arp/print", array(
		"?address" => "$ipbinding",
	));
	$sarp = $getarp[0]['.id'];

	$API->comm("/ip/arp/remove", array(
		".id" => "$sarp",
	));

	$getlease = $API->comm("/ip/dhcp-server/lease/print", array(
		"?address" => "$ipbinding",
	));

	$slease = $getlease[0]['.id'];

	$API->comm("/ip/dhcp-server/lease/remove", array(
		".id" => "$slease",
	));			
		
//redirect to ipbinding
	$redirect = "./?hotspot=ipbinding&session=" . $session;
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";
}

// enable ip binging
elseif ($enableipbinding != "") {
	include_once('./lib/router/RouterService.php');
	$router = new RouterService($API);
	$router->setHotspotIpBindingDisabled($enableipbinding, false);

	$redirect = "./?hotspot=ipbinding&session=" . $session;
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";
}

// disable ip binging
elseif ($disableipbinding != "") {
	include_once('./lib/router/RouterService.php');
	$router = new RouterService($API);
	$router->setHotspotIpBindingDisabled($disableipbinding, true);

	$redirect = "./?hotspot=ipbinding&session=" . $session;
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";
}