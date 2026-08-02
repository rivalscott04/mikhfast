<?php

if (!function_exists('mikhmon_report_idhr_to_iso')) {
	function mikhmon_report_idhr_to_iso($idhr) {
		if (!is_string($idhr) || $idhr === "") return "";
		$parts = explode("/", $idhr);
		if (count($parts) !== 3) return "";
		$mon = strtolower(trim($parts[0]));
		$day = trim($parts[1]);
		$year = trim($parts[2]);
		$monMap = array(
			"jan" => "01", "feb" => "02", "mar" => "03", "apr" => "04",
			"may" => "05", "jun" => "06", "jul" => "07", "aug" => "08",
			"sep" => "09", "oct" => "10", "nov" => "11", "dec" => "12",
		);
		if (!isset($monMap[$mon])) return "";
		$mm = $monMap[$mon];
		if (strlen($day) === 1) $day = "0" . $day;
		if (strlen($day) !== 2) return "";
		if (strlen($year) !== 4) return "";
		return $year . "-" . $mm . "-" . $day;
	}
}

if (!function_exists('mikhmon_report_normalize_date')) {
	function mikhmon_report_normalize_date($dateStr) {
		$s = is_string($dateStr) ? trim($dateStr) : "";
		if ($s === "") return "";
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
			$y = substr($s, 0, 4);
			$m = substr($s, 5, 2);
			$d = substr($s, 8, 2);
			$monMap = array(
				"01" => "jan", "02" => "feb", "03" => "mar", "04" => "apr",
				"05" => "may", "06" => "jun", "07" => "jul", "08" => "aug",
				"09" => "sep", "10" => "oct", "11" => "nov", "12" => "dec",
			);
			$mon = isset($monMap[$m]) ? $monMap[$m] : "";
			return $mon ? ($mon . "/" . $d . "/" . $y) : $s;
		}
		return $s;
	}
}

if (!function_exists('mikhmon_report_row_date')) {
	function mikhmon_report_row_date($row) {
		if (!is_array($row)) return "";
		if (isset($row["source_date"]) && $row["source_date"] !== "") {
			return (string) $row["source_date"];
		}
		if (isset($row["source"]) && $row["source"] !== "") {
			return (string) $row["source"];
		}
		if (!isset($row["name"])) return "";
		$parts = explode("-|-", $row["name"]);
		return isset($parts[0]) ? (string) $parts[0] : "";
	}
}

if (!function_exists('mikhmon_report_matches_day')) {
	function mikhmon_report_matches_day($dateStr, $idhr) {
		if ($idhr === "" || $dateStr === "") return false;
		$idhrIso = mikhmon_report_idhr_to_iso($idhr);
		$target = mikhmon_report_normalize_date($idhr);
		$norm = mikhmon_report_normalize_date($dateStr);
		if ($norm !== "" && $target !== "" && $norm === $target) return true;
		if ($dateStr === $idhr) return true;
		if ($idhrIso !== "" && $dateStr === $idhrIso) return true;
		return false;
	}
}

if (!function_exists('mikhmon_report_matches_month')) {
	function mikhmon_report_matches_month($dateStr, $idbl) {
		if ($idbl === "" || $dateStr === "") return false;
		$norm = mikhmon_report_normalize_date($dateStr);
		if ($norm === "") return false;
		$parts = explode("/", $norm);
		if (count($parts) !== 3) return false;
		return (strtolower($parts[0]) . $parts[2]) === strtolower($idbl);
	}
}

if (!function_exists('mikhmon_report_row_matches_day')) {
	function mikhmon_report_row_matches_day($row, $idhr) {
		return mikhmon_report_matches_day(mikhmon_report_row_date($row), $idhr);
	}
}

if (!function_exists('mikhmon_report_row_matches_month')) {
	function mikhmon_report_row_matches_month($row, $idbl) {
		if (is_array($row) && isset($row["owner_key"]) && $row["owner_key"] === $idbl) {
			return true;
		}
		if (is_array($row) && isset($row["owner"]) && $row["owner"] === $idbl) {
			return true;
		}
		return mikhmon_report_matches_month(mikhmon_report_row_date($row), $idbl);
	}
}

if (!function_exists('mikhmon_report_fetch_scripts')) {
	function mikhmon_report_fetch_scripts($API, $idhr = "", $idbl = "") {
		$params = array(
			"?comment" => "mikhmon",
			".proplist" => ".id,name,source,owner",
		);
		if (strlen($idbl) > 0) {
			$params["?owner"] = $idbl;
		} elseif (strlen($idhr) > 0) {
			$params["?source"] = $idhr;
		}
		$rows = $API->comm("/system/script/print", $params);
		if (!is_array($rows)) return array();

		if (strlen($idhr) > 0) {
			$filtered = array();
			for ($i = 0; $i < count($rows); $i++) {
				if (mikhmon_report_row_matches_day($rows[$i], $idhr)) {
					$filtered[] = $rows[$i];
				}
			}
			return $filtered;
		}

		if (strlen($idbl) > 0) {
			$filtered = array();
			for ($i = 0; $i < count($rows); $i++) {
				if (mikhmon_report_row_matches_month($rows[$i], $idbl)) {
					$filtered[] = $rows[$i];
				}
			}
			return $filtered;
		}

		return $rows;
	}
}

require_once __DIR__ . '/mikhmon-report-db.php';

if (!function_exists('mikhmon_report_fetch')) {
	function mikhmon_report_fetch($API, $routerSlug, $idhr = "", $idbl = "") {
		if (mikhmon_db_enabled()) {
			if ($API) {
				mikhmon_report_sync_if_stale($API, $routerSlug);
			}
			return mikhmon_report_fetch_db($routerSlug, $idhr, $idbl);
		}
		return mikhmon_report_fetch_scripts($API, $idhr, $idbl);
	}
}

if (!function_exists('mikhmon_report_remove_filter')) {
	function mikhmon_report_remove_filter($API, $routerSlug, $idhr = "", $idbl = "") {
		$rows = mikhmon_report_fetch($API, $routerSlug, $idhr, $idbl);
		return mikhmon_report_remove_rows($API, $routerSlug, $rows);
	}
}

if (!function_exists('mikhmon_report_parse_name')) {
	function mikhmon_report_parse_name($name) {
		$parts = explode("-|-", (string) $name);
		$parsed = array(
			"date" => isset($parts[0]) ? mikhmon_report_normalize_date($parts[0]) : "",
			"time" => isset($parts[1]) ? $parts[1] : "",
			"user" => isset($parts[2]) ? $parts[2] : "",
			"price" => isset($parts[3]) ? $parts[3] : "",
			"profile" => "",
			"comment" => "",
		);
		if (isset($parts[8])) {
			$parsed["profile"] = isset($parts[7]) ? $parts[7] : "";
			$parsed["comment"] = $parts[8];
		} else {
			$parsed["comment"] = isset($parts[7]) ? $parts[7] : "";
		}
		return $parsed;
	}
}

if (!function_exists('mikhmon_report_row_timestamp')) {
	function mikhmon_report_row_timestamp($row) {
		if (is_array($row) && isset($row["sold_at"]) && $row["sold_at"] !== null && $row["sold_at"] !== "") {
			return (int) $row["sold_at"];
		}
		$dateStr = mikhmon_report_row_date($row);
		if ($dateStr === "") return null;
		if (preg_match('/^\d{4}-\d{2}-\d{2}/', $dateStr)) {
			$ts = strtotime(substr($dateStr, 0, 10));
			return $ts ? (int) $ts : null;
		}
		$norm = mikhmon_report_normalize_date($dateStr);
		$iso = mikhmon_report_idhr_to_iso($norm);
		if ($iso === "") return null;
		$ts = strtotime($iso);
		return $ts ? (int) $ts : null;
	}
}

if (!function_exists('mikhmon_report_count_older_than')) {
	function mikhmon_report_count_older_than($scripts, $days) {
		if (!is_array($scripts) || $days < 1) return 0;
		$cutoff = time() - ((int) $days * 86400);
		$count = 0;
		for ($i = 0; $i < count($scripts); $i++) {
			$ts = mikhmon_report_row_timestamp($scripts[$i]);
			if ($ts !== null && $ts < $cutoff) {
				$count++;
			}
		}
		return $count;
	}
}

if (!function_exists('mikhmon_report_filter_older_than')) {
	function mikhmon_report_filter_older_than($scripts, $days) {
		if (!is_array($scripts) || $days < 1) return array();
		$cutoff = time() - ((int) $days * 86400);
		$out = array();
		for ($i = 0; $i < count($scripts); $i++) {
			$ts = mikhmon_report_row_timestamp($scripts[$i]);
			if ($ts !== null && $ts < $cutoff) {
				$out[] = $scripts[$i];
			}
		}
		return $out;
	}
}

?>
