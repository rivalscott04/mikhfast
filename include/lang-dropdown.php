<?php
/**
 * Navbar language picker (custom dropdown).
 *
 * Expects: $url, $langid, $isocodelang; optional $language (label).
 */
if (!isset($url)) $url = "";
if (!isset($langid) || $langid === "") $langid = "en";
if (!isset($isocodelang) || !is_array($isocodelang)) {
  @include_once(__DIR__ . '/../lang/isocodelang.php');
}
if (!isset($isocodelang) || !is_array($isocodelang)) $isocodelang = array();

$mmLangBase = explode("&setlang", $url)[0];
$mmLangCurrent = (string) $langid;
$mmLangCurrentLabel = isset($isocodelang[$mmLangCurrent])
  ? (string) $isocodelang[$mmLangCurrent]
  : strtoupper($mmLangCurrent);
$mmLangAria = isset($language) ? (string) $language : "Language";

$mmLangFiles = glob(__DIR__ . '/../lang/*.php');
$mmLangOptions = array();
if (is_array($mmLangFiles)) {
  foreach ($mmLangFiles as $mmLangFile) {
    if (!is_file($mmLangFile)) continue;
    $mmLangCode = basename($mmLangFile, '.php');
    if ($mmLangCode === 'isocodelang') continue;
    if (!isset($isocodelang[$mmLangCode])) continue;
    $mmLangOptions[] = array(
      'code' => $mmLangCode,
      'label' => (string) $isocodelang[$mmLangCode],
      'url' => $mmLangBase . '&setlang=' . rawurlencode($mmLangCode),
    );
  }
}
usort($mmLangOptions, function ($a, $b) {
  return strcasecmp($a['label'], $b['label']);
});
?>

<div class="mm-lang-dropdown" data-mm-lang-dropdown<?= isset($_loading) ? ' data-loading-msg="' . htmlspecialchars($_loading, ENT_QUOTES) . '"' : ''; ?>>
  <button
    type="button"
    class="mm-lang-dropdown__trigger"
    aria-haspopup="listbox"
    aria-expanded="false"
    aria-label="<?= htmlspecialchars($mmLangAria, ENT_QUOTES); ?>"
    title="<?= htmlspecialchars($mmLangAria, ENT_QUOTES); ?>"
  >
    <i class="fa fa-globe" aria-hidden="true"></i>
    <span class="mm-lang-dropdown__code"><?= htmlspecialchars(strtoupper($mmLangCurrent), ENT_QUOTES); ?></span>
    <i class="fa fa-caret-down mm-lang-dropdown__caret" aria-hidden="true"></i>
  </button>
  <div class="mm-lang-dropdown__menu" role="listbox" aria-label="<?= htmlspecialchars($mmLangAria, ENT_QUOTES); ?>" hidden>
    <?php foreach ($mmLangOptions as $mmLangOpt) {
      $mmIsActive = ($mmLangOpt['code'] === $mmLangCurrent);
      ?>
      <button
        type="button"
        class="mm-lang-dropdown__item<?= $mmIsActive ? ' mm-lang-dropdown__item--active' : ''; ?>"
        role="option"
        aria-selected="<?= $mmIsActive ? 'true' : 'false'; ?>"
        data-lang-url="<?= htmlspecialchars($mmLangOpt['url'], ENT_QUOTES); ?>"
      >
        <span class="mm-lang-dropdown__label"><?= htmlspecialchars($mmLangOpt['label'], ENT_QUOTES); ?></span>
        <span class="mm-lang-dropdown__badge"><?= htmlspecialchars(strtoupper($mmLangOpt['code']), ENT_QUOTES); ?></span>
        <?php if ($mmIsActive) { ?><i class="fa fa-check mm-lang-dropdown__check" aria-hidden="true"></i><?php } ?>
      </button>
    <?php } ?>
  </div>
</div>
