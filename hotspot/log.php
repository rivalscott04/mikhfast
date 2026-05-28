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
if (!isset($_SESSION["mikhmon"])) {
	header("Location:../admin.php?id=login");
} else {

	if (!isset($router)) {
		include_once('./lib/router/RouterService.php');
		$router = new RouterService($API);
	}
	// Loading the full RouterOS log can be very slow on busy routers.
	// Default to a reasonable limit; allow explicit full load via ?all=1.
	$all = isset($_GET['all']) ? (string) $_GET['all'] : "0";
	$limit = 200;
	if ($all === "1") {
		$log = $router->getHotspotLogsAll();
	} else {
		$log = $router->getHotspotLogs($limit);
	}
	$TotalReg = is_array($log) ? count($log) : 0;

	function __mikhmon_parse_hotspot_log_row($row)
	{
		if (!is_array($row) || !isset($row['message'])) return null;
		$msg = (string) $row['message'];
		// only show messages created by hotspot logging prefix "->"
		if (substr($msg, 0, 2) !== "->") return null;
		$mess = explode(":", $msg);
		$time = isset($row['time']) ? (string) $row['time'] : "";

		$userIp = "";
		if (count($mess) > 6) {
			$userIp = $mess[1] . ":" . $mess[2] . ":" . $mess[3] . ":" . $mess[4] . ":" . $mess[5] . ":" . $mess[6];
		} elseif (count($mess) > 1) {
			$userIp = $mess[1];
		}

		$detail = "";
		if (count($mess) > 10) {
			$detail = str_replace("trying to", "", $mess[7] . " " . $mess[8] . " " . $mess[9] . " " . $mess[10]);
		} elseif (count($mess) > 5) {
			$detail = str_replace("trying to", "", $mess[2] . " " . $mess[3] . " " . $mess[4] . " " . $mess[5]);
		}

		return array(
			'time' => $time,
			'userIp' => trim($userIp),
			'detail' => trim($detail),
		);
	}
}
?>
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-header">
    <h3>
		<i class=" fa fa-align-justify"></i> <?= $_hotspot_log ?>
		<?php if ($all !== "1") { ?>
			<small style="opacity:.85;">(showing latest 200)</small>
		<?php } else { ?>
			<small style="opacity:.85;">(showing all)</small>
		<?php } ?>
		&nbsp; | &nbsp;&nbsp;<i onclick="location.reload();" class="fa fa-refresh pointer " title="Reload data"></i>
		<?php if ($all !== "1") { ?>
			&nbsp; | &nbsp;&nbsp;<a class="pointer" href="./?hotspot=log&session=<?= $session; ?>&all=1" title="Load all logs (may be slow)">Load all</a>
		<?php } ?>
	</h3>
</div>
<div class="card-body">

<div style="max-width: 350px;">
    <input id="filterTable" type="text" class="form-control" placeholder="Search.."> 
</div>
<div id="hotspotLogScroll" style="padding: 5px; max-height: 75vh;" class="mr-t-10 overflow">
<table class="table table-sm table-bordered table-hover" id="dataTable" >
	<thead>
        <tr>
            <th><?= $_time ?></th>
            <th><?= $_users ?> (IP)</th>
            <th><?= $_messages ?></th>
        </tr>
    </thead>
	<tbody id="hotspotLogBody" data-session="<?= htmlspecialchars($session, ENT_QUOTES) ?>" data-offset="<?= ($all === "1") ? (int) $TotalReg : (int) $limit ?>" data-limit="<?= (int) $limit ?>" data-all="<?= htmlspecialchars($all, ENT_QUOTES) ?>">
<?php
for ($i = 0; $i < $TotalReg; $i++) {
	$parsed = __mikhmon_parse_hotspot_log_row($log[$i]);
	if ($parsed === null) continue;
	echo "<tr>";
	echo "<td>" . htmlspecialchars($parsed['time'], ENT_QUOTES) . "</td>";
	echo "<td>" . htmlspecialchars($parsed['userIp'], ENT_QUOTES) . "</td>";
	echo "<td>" . htmlspecialchars($parsed['detail'], ENT_QUOTES) . "</td>";
	echo "</tr>";
}
?>
		<tr id="hotspotLogLoadingRow" style="display:none;">
			<td colspan="3" class="text-center">
				<i class="fa fa-circle-o-notch fa-spin"></i> <?= $_processing ?>
			</td>
		</tr>
	</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php if ($all !== "1") { ?>
<script>
(function () {
  var body = document.getElementById("hotspotLogBody");
  var scrollEl = document.getElementById("hotspotLogScroll");
  var loadingRow = document.getElementById("hotspotLogLoadingRow");
  if (!body || !scrollEl || !loadingRow) return;

  var session = body.getAttribute("data-session") || "";
  var offset = parseInt(body.getAttribute("data-offset") || "0", 10);
  var limit = parseInt(body.getAttribute("data-limit") || "200", 10);
  var allMode = body.getAttribute("data-all") || "0";
  if (!session || allMode === "1") return;

  var isLoading = false;
  var hasMore = true;

  function setLoading(on) {
    loadingRow.style.display = on ? "" : "none";
  }

  function esc(s) {
    s = (s === null || s === undefined) ? "" : String(s);
    return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");
  }

  function appendRows(rows) {
    if (!rows || !rows.length) return;
    for (var i = 0; i < rows.length; i++) {
      var r = rows[i] || {};
      var tr = document.createElement("tr");
      tr.innerHTML =
        "<td>" + esc(r.time) + "</td>" +
        "<td>" + esc(r.userIp) + "</td>" +
        "<td>" + esc(r.detail) + "</td>";
      body.insertBefore(tr, loadingRow);
    }
  }

  function loadMore() {
    if (isLoading || !hasMore) return;
    isLoading = true;
    setLoading(true);

    var url = "./hotspot/log_data.php?session=" + encodeURIComponent(session) +
      "&offset=" + encodeURIComponent(String(offset)) +
      "&limit=" + encodeURIComponent(String(limit));

    fetch(url, { credentials: "same-origin" })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || data.ok !== true) throw new Error("bad response");
        appendRows(data.rows || []);
        offset = typeof data.nextOffset === "number" ? data.nextOffset : (offset + (data.rows ? data.rows.length : 0));
        hasMore = data.hasMore === true;
      })
      .catch(function () {
        // stop trying to avoid hammering the router on repeated failures
        hasMore = false;
      })
      .finally(function () {
        isLoading = false;
        setLoading(false);
      });
  }

  function nearBottom() {
    // when within 250px of bottom, fetch next page
    return (scrollEl.scrollTop + scrollEl.clientHeight) >= (scrollEl.scrollHeight - 250);
  }

  scrollEl.addEventListener("scroll", function () {
    if (nearBottom()) loadMore();
  });

  // in case the initial content doesn't fill the viewport
  setTimeout(function () {
    if (nearBottom()) loadMore();
  }, 50);
})();
</script>
<?php } ?>
