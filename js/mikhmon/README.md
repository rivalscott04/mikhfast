# Mikhmon JavaScript modules

Front-end code is split by responsibility. **Load order matters** — use `include/mikhmon-scripts.php` (already wired in `index.php` and `admin.php`).

| File | Role |
|------|------|
| `legacy-forms.js` | `RequiredV`, `defUserl` (hotspot generate forms) |
| `notify.js` | Legacy `#notify` bar (`notify`, `notifyHide`) |
| `ui-toast.js` | Modern toast + `mm-switching` body class |
| `ui-skeleton.js` | SPA navigation skeleton templates |
| `ui-session.js` | `connect`, `stheme`, `loadpage`, session switch helpers |
| `table-sort.js` | `sortTable`, `makeSortable`, `makeAllSortable` |
| `spa-intervals.js` | Clear dashboard/traffic intervals |
| `widget-applog.js` | Dashboard app log poller |
| `widget-traffic.js` | Highcharts traffic monitor |
| `spa-router.js` | AJAX navigation, `mikhmon_applyHtml`, form POST hijack |
| `widget-accordion.js` | Sidebar dropdown accordion |
| `widget-lang.js` | Navbar language dropdown |
| `widget-form-select.js` | Light-theme custom `<select>` UI |
| `bootstrap.js` | Idle logout timer, SPA listeners, `DOMContentLoaded` init |

The previous single file is kept as `js/mikhmon.bundle.legacy.js` for reference only.

All public functions remain on `window` so inline `onclick` handlers in PHP templates keep working.
