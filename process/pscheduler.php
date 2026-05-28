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

// remove scheduler
if ($removesch != "") {
	include_once('./lib/router/RouterService.php');
	$router = new RouterService($API);
	$router->removeSchedulerById($removesch);

	$redirect = "./?system=scheduler&session=" . $session;
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";
}
// enable scheduler
elseif ($enablesch != "") {
	include_once('./lib/router/RouterService.php');
	$router = new RouterService($API);
	$router->setSchedulerDisabled($enablesch, false);

	$redirect = "./?system=scheduler&session=" . $session;
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";
}

// disable scheduler
elseif ($disablesch != "") {
	include_once('./lib/router/RouterService.php');
	$router = new RouterService($API);
	$router->setSchedulerDisabled($disablesch, true);

	$redirect = "./?system=scheduler&session=" . $session;
	if (mikhmon_is_ajax()) {
		mikhmon_json(array(
			"ok" => true,
			"flash" => "OK",
			"redirect" => $redirect,
		));
	}
	echo "<script>window.location='" . $redirect . "'</script>";
}