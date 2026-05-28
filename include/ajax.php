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

function mikhmon_debug_enabled() {
  return isset($_GET['debug']) && $_GET['debug'] == '1';
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

// If debug=1 and this is an AJAX request, surface fatal errors as JSON.
if (mikhmon_is_ajax() && mikhmon_debug_enabled()) {
  error_reporting(E_ALL);
  @ini_set('display_errors', '1');
  @ini_set('display_startup_errors', '1');

  register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) return;
    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
    if (!in_array($err['type'], $fatalTypes, true)) return;

    if (!headers_sent()) {
      http_response_code(500);
      header('Content-Type: application/json; charset=utf-8');
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }
    echo json_encode(array(
      "ok" => false,
      "fatal" => true,
      "error" => array(
        "type" => $err['type'],
        "message" => $err['message'],
        "file" => $err['file'],
        "line" => $err['line'],
      ),
    ));
  });
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

