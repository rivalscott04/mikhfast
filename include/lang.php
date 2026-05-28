<?php
// Default language; overridden by session (setlang) or this file on disk.
$langid = "en";
if (function_exists("session_status") && session_status() === PHP_SESSION_ACTIVE) {
  if (!empty($_SESSION["lang"]) && is_string($_SESSION["lang"])) {
    $langid = $_SESSION["lang"];
  }
}
