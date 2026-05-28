<?php
/**
 * Minimal helpers for AJAX/JSON responses.
 * Kept dependency-free to work in older PHP setups.
 */

function mikhmon_is_ajax() {
  if (isset($_GET['ajax']) && $_GET['ajax'] == '1') return true;
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
  if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) return true;
  return false;
}

function mikhmon_json($payload, $statusCode = 200) {
  if (!headers_sent()) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  }
  echo json_encode($payload);
  exit;
}

function mikhmon_extract_wrapper_html($html) {
  // Best-effort extraction: wrapper contents until </body>.
  $start = strpos($html, '<div class="wrapper">');
  if ($start === false) $start = strpos($html, "<div class='wrapper'>");

  $end = stripos($html, '</body>');
  if ($end === false) $end = strlen($html);

  if ($start === false) {
    return substr($html, 0, $end);
  }

  return substr($html, $start, $end - $start);
}

