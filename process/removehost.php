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

// remove host
include_once('./lib/router/RouterService.php');
$router = new RouterService($API);
$router->removeHotspotHost($removehost);
// redirect to host
$redirect = "./?hotspot=hosts&session=" . $session;
mikhmon_redirect_success($redirect, mikhmon_t('_toast_operation_ok'));
?>