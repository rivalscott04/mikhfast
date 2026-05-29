<?php
function mikhmon_voucher_logo_url($session, $cacheBust = true) {
  $base = '../img/';
  $sessionLogo = $base . 'logo-' . $session . '.png';

  if (file_exists($sessionLogo)) {
    if ($cacheBust) {
      return $sessionLogo . '?t=' . str_replace(' ', '_', date('Y-m-d H:i:s'));
    }
    return $sessionLogo;
  }

  return $base . 'mikfast.svg';
}
