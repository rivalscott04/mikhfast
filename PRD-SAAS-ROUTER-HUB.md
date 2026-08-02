# PRD — SaaS Router Hub & Multi-Router Management

**Produk:** MIKFAST (mikhmonnew)  
**Versi dokumen:** 1.2  
**Tanggal:** 2026-08-02  
**Status:** Fase 1–5b **selesai** (core SaaS); beberapa item P2 / non-goals belum  
**Changelog v1.2:** Status implementasi per fase; tambah Fase 5b Super Admin; centang acceptance criteria yang sudah done  
**Changelog v1.1:** Tambah mitigasi storage MikroTik (F7), HDD metric di health check, wizard storage warning  
**Referensi UX:** diskusi SaaS multi-tenant (subdomain per tenant, unlimited router per tenant)  
**Referensi desain:** `UI-UX-AUDIT.md`, `.cursor/rules/mikfast-design.mdc`

---

## 1. Ringkasan Eksekutif

MIKFAST saat ini masih beroperasi seperti instalasi single-tenant: router disimpan sebagai "session" di `include/config.php`, daftar router ada di Admin Settings (`settings/sessions.php`), dan switcher berupa `<select>` di sidebar. Pola ini tidak scalable untuk SaaS di mana setiap tenant (`tenant.mikfast.com`) mengelola banyak router.

PRD ini mendefinisikan **Router Hub** — halaman pusat manajemen router — beserta wizard tambah router, router switcher yang lebih baik, dan pemisahan konteks workspace vs router. Implementasi **wajib reuse komponen UI existing**; tidak menambah design system baru kecuali class `mm-*` yang benar-benar diperlukan (lihat design rule).

---

## Status Implementasi (snapshot 2026-08-02)

| Fase | Status | Ringkasan |
|------|--------|-----------|
| **Fase 1** — Router Hub foundation | ✅ Selesai | Hub, redirect login, kartu router, sessions refactor |
| **Fase 2** — Add Router Wizard | ✅ Selesai | Wizard 3 langkah, test koneksi, save config |
| **Fase 3** — Router Switcher | ✅ Selesai | Panel ganti dropdown, display name, SPA switch |
| **Fase 4** — Polish & Storage L1 | ✅ Selesai | Search/filter, offline banner, HDD chip, i18n EN/ID |
| **Fase 4b** — Storage mitigation L2 | ✅ Selesai | `ensureHotspotLoggingSafe`, log degrade, purge manual |
| **Fase 5** — Multi-tenant backend | ✅ Selesai (core) | Subdomain tenant, SQLite, report/log DB, cron maintenance |
| **Fase 5b** — Super Admin panel | ✅ Selesai | `admin.{domain}` — CRUD tenant, suspend, env-only auth |
| **Fase 6** — Backlog SaaS polish | ✅ Selesai | Lokasi, limit 5, off-router, notify, log ingest, Enter switcher |

**Tests otomatis:** 155 assertion pass (`npm test` — hub, wizard, switcher, storage, report-db, tenant, superadmin).

**Dokumen deploy:** [DEPLOY.md](./DEPLOY.md) — panduan step-by-step DNS wildcard, Nginx, PHP-FPM env.

| **Non-goals v1** | ⏸️ Out of scope | Billing, team, mobile app |

### Selesai di Fase 6 (2026-08-02)

| Item | Status |
|------|--------|
| Field **Lokasi** wizard → config index `@loc@` | ✅ |
| **Enter / Arrow** di router switcher | ✅ |
| **Push notif** offline/storage (webhook + Telegram) | ✅ |
| **Limit router 5** per tenant (DB `tenant_meta`, override env) | ✅ |
| **Off-router reports** — `/tool fetch` → `report-ingest` (bukan script di MT) | ✅ |
| **Log webhook** — `log-ingest` + cron sync hotspot logs ke SQLite | ✅ |

---

### Tujuan

| # | Tujuan | Ukuran sukses | Status |
|---|--------|---------------|--------|
| G1 | Tenant bisa melihat semua router dalam satu layar | Router Hub menampilkan status + metrik ringkas per router | ✅ |
| G2 | Onboarding router pertama ≤ 3 menit | Wizard 3 langkah + test koneksi sebelum save | ✅ |
| G3 | Switch router cepat meski > 5 router | Switcher searchable menggantikan dropdown mentah | ✅ |
| G4 | UX konsisten dengan MIKFAST existing | Zero library UI baru; pakai `card`, `box`, `mm-*` yang sudah ada | ✅ |
| G5 | Siap multi-tenant subdomain | Data router terisolasi per tenant | ✅ (Fase 5) |
| G6 | Operator aware storage router sebelum kritis | HDD metric di Hub + warning di wizard/dashboard | ✅ (Fase 4/4b) |

### Non-goals (v1)

- Billing/subscription UI penuh (hanya placeholder limit counter)
- Team/multi-user per tenant
- Notifikasi email/WA saat router offline
- Mobile app native
- Migrasi otomatis dari self-hosted ke SaaS (manual/onboarding terpisah)

---

## 2. Konteks & Masalah

### Keadaan sekarang (post-implementasi)

```
Login → Router Hub (admin.php?id=routers)
Router Hub → kartu router + status + storage chip
Wizard (admin.php?id=router-add) → tambah router 3 langkah
Sidebar → panel switcher (bukan <select>)
Account Settings (admin.php?id=sessions) → admin tenant saja
Super Admin (admin.{domain}) → kelola tenant SaaS
Data per tenant → data/tenants/{slug}/ (config + SQLite)
```

### Keadaan lama (sebelum Router Hub — referensi)

```
Login → langsung masuk 1 router (session aktif)
Admin Settings (admin.php?id=sessions)
  ├── Daftar router (box warna acak, rand color)
  └── Form admin (password, quick print) — campur level workspace
Sidebar
  └── <select class="mm-sidenav-session"> — switch router
```

### Pain points

1. **Istilah "session"** membingungkan — user pikir browser session, bukan router.
2. **Dropdown sidebar** tidak scalable; tidak ada search; hanya menampilkan slug internal.
3. **Tidak ada status router** di daftar — user tidak tahu online/offline sebelum buka.
4. **Add router** butuh paham slug internal + form teknis sekaligus.
5. **Admin settings + router list campur** — level workspace vs level router tidak jelas.
6. **Warna acak** (`$color[rand(1,11)]`) — identitas router tidak konsisten antar reload.

### Peluang SaaS

- Subdomain = workspace identity (`rival.mikfast.com`)
- Router Hub = landing page setelah login (bukan langsung deep ke 1 router)
- Plan limit visible (`3/10 router`) — fondasi monetisasi

---

## 3. Persona & Use Case

### Persona A — Operator hotspot (primary)

- Mengelola 1–5 lokasi (warung, kos, cafe)
- Akses mostly dari HP
- Butuh: lihat status cepat, switch lokasi, tambah router tanpa paham teknis

### Persona B — Reseller/ISP kecil (secondary)

- 10–50 router under satu akun
- Butuh: search/filter, status aggregate, onboarding cepat per client

### Use case utama

| ID | Use case | Actor | Prioritas |
|----|----------|-------|-----------|
| UC-01 | Lihat semua router + status | Operator | P0 |
| UC-02 | Tambah router pertama (empty state) | Operator baru | P0 |
| UC-03 | Tambah router ke-N | Operator | P0 |
| UC-04 | Switch ke router lain | Operator | P0 |
| UC-05 | Edit koneksi router | Operator | P0 |
| UC-06 | Hapus router | Operator | P1 |
| UC-07 | Buka dashboard router | Operator | P0 |
| UC-08 | Router offline — tampil graceful | Operator | P1 |
| UC-09 | Lihat plan limit router | Reseller | P2 |
| UC-10 | Storage router hampir penuh — warning + cleanup | Operator | P1 |

---

## 4. Arsitektur Informasi

### Hierarki navigasi (target)

```
tenant.mikfast.com
│
├── /admin.php?id=routers          ← Router Hub (landing setelah login)
│
├── Workspace Settings             ← pisah dari router
│   ├── admin.php?id=sessions      ← rename label: "Account Settings"
│   ├── admin.php?id=uplogo        ← branding tenant
│   └── (future: billing, team)
│
└── Router context (?session=slug)
    ├── Dashboard (./?session=X)
    ├── Hotspot, Log, Report, System
    └── Router Settings (admin.php?id=settings&session=X)
```

### Terminologi (user-facing)

| Internal (code) | User-facing (UI) | Catatan |
|-----------------|------------------|---------|
| `session` / `$sesname` | **Router** | Label UI saja; slug internal tetap `session` di URL/code untuk backward compat |
| `$data[$session][4]` hotspot name | **Nama hotspot** | |
| `board-name` / identity | **Model perangkat** | dari `RouterService` |
| `admin.php?id=sessions` | **Pengaturan Akun** | bukan "Admin Settings" generik |

---

## 5. Spesifikasi Fitur

### F1 — Router Hub (`admin.php?id=routers`)

**Deskripsi:** Halaman daftar semua router tenant. Landing page default setelah login sukses (ganti redirect ke `id=sessions`).

**Layout:** reuse grid existing (`row` + `col-4` / `col-6` responsive per breakpoint tablet di `mikhmon-custom.css`).

**Header halaman:**
- Title: `Router` (i18n key baru: `$_routers`)
- Subtitle: `{subdomain}.mikfast.com · {n}/{limit} router` (limit hardcode dulu atau dari config tenant)
- Primary CTA: tombol `+ Tambah Router` → wizard (F2)

**Kartu router (ganti box warna acak):**

Reuse struktur `card` + pola visual `mm-kpi` / `mm-dashheader`:

```
┌ card ─────────────────────────────┐
│ ● Online          [mm-chip--ok]   │
│ Kos Coffee Shop                   │  ← display name (hotspot name)
│ RB4011 · RouterOS 7              │  ← board + ROS (cached)
│ 12 aktif · 170 user              │  ← optional, dari cache/API
│ ⚠ Storage 92% penuh [mm-chip--warn] │  ← jika hdd_free_pct ≤ 25%
│ [Buka]  [Edit]  [⋯]               │
└───────────────────────────────────┘
```

**Status indicator:** reuse `mm-chip mm-chip--ok` (online) / `mm-chip mm-chip--muted` (offline/unknown) — sama seperti `include/dashboard-header.php`.

**Empty state (0 router):**
- `card` centered, icon `fa-server`
- Copy: "Belum ada router terhubung"
- CTA: `+ Tambah Router Pertama`
- Link sekunder: panduan setup API MikroTik (external doc, tab baru)

**Search/filter (≥ 4 router):**
- Input `form-control` dengan icon search
- Filter status: Semua / Online / Offline — reuse `<select class="form-control">`
- Client-side filter dulu (v1); server-side nanti

**Actions per kartu:**
| Aksi | Destinasi | Komponen |
|------|-----------|----------|
| Buka | `./?session={slug}` | `btn` primary / `mm-btn-ghost` |
| Edit | `admin.php?id=settings&session={slug}` | link |
| Hapus | `mikhmon_confirm()` → remove-session | `mm-action-btn--danger` |

**Offline state:**
- Kartu tetap tampil; chip "Offline"
- Subtext: "Terakhir online: {relative time}" jika ada cache
- Tombol "Coba reconnect" → hit test-connection endpoint

**Acceptance criteria:**
- [x] Login sukses redirect ke Router Hub, bukan sessions
- [x] Semua router tenant tampil dengan nama display (bukan slug mentah)
- [x] Status online/offline terlihat tanpa buka router
- [x] Empty state + CTA jika belum ada router
- [x] Mobile: 1 kolom kartu; tombol Buka min 44px tap target
- [x] Tidak pakai `$color[rand()]` — hapus dari implementasi hub
- [x] Kartu router menampilkan chip storage warning jika `hdd_free_pct ≤ 25%` (F5/F7)

---

### F2 — Wizard Tambah Router (3 langkah)

**Entry points:**
- Router Hub → `+ Tambah Router`
- Empty state CTA
- Sidebar switcher → `+ Tambah router`

**Route:** `admin.php?id=router-add` (baru) atau extend `admin.php?id=settings&router=new-*` dengan UI wizard.

**Langkah 1 — Identitas**

| Field | Required | Validasi | Catatan |
|-------|----------|----------|---------|
| Nama router | Ya | 2–50 char | → `$shotspotname` / display name |
| Lokasi | Tidak | max 100 char | metadata baru (optional v1: simpan di comment field atau DB nanti) |
| Slug internal | Auto | `[a-z0-9-]` | auto dari nama; editable di "Lanjutan" saja |

Reuse: `form-control`, layout `table` form existing di `settings/settings.php` ATAU `mm-user-form-row` pattern.

**Langkah 2 — Koneksi**

| Field | Required | Maps to existing |
|-------|----------|------------------|
| Alamat router (IP:port) | Ya | `$siphost` |
| Username API | Ya | `$suserhost` |
| Password API | Ya | `$spasswdhost` |

**Test koneksi (wajib sebelum lanjut):**
- Tombol `Test Koneksi` → AJAX endpoint baru `admin.php?id=router-test` (atau action POST)
- Backend: reuse `RouterService` + `routeros_api.class.php`
- Sukses: toast `mikhmon_toast` success + tampilkan board name + ROS version + **storage summary** (`free / total`, `hdd_free_pct`)
- Gagal: toast error dengan pesan spesifik (timeout, auth failed, port closed)
- Jika `hdd_free_pct ≤ 25%`: tampilkan `alert alert-warning` inline dengan rekomendasi (lihat F7)

UI feedback sukses: reuse `alert alert-success` inline atau `mm-chip mm-chip--ok`.

**Langkah 3 — Hotspot (opsional / bisa skip)**

| Field | Default | Maps to existing |
|-------|---------|------------------|
| Nama hotspot | = Nama router | `$shotspotname` |
| Interface | auto-detect | `$siface` — populate dari hasil test koneksi |
| Auto-refresh | 10 detik | `$sreload` |
| DNS, Currency, Idle timeout | default existing | field hidden/default |

Tombol: `[Simpan & Buka Dashboard]` (primary) · `[Atur Nanti]` (skip ke hub)

**Acceptance criteria:**
- [x] User tidak bisa submit tanpa test koneksi sukses (langkah 2)
- [x] Wizard bisa di-navigasi maju/mundur tanpa kehilangan input
- [x] Setelah save, redirect ke `./?session={slug}` + toast sukses
- [x] Backward compat: data tersimpan format `$data` existing di config per tenant
- [x] Mobile: form label di atas input (reuse breakpoint `@media max-width:480px` di `mikhmon-custom.css`)
- [ ] Field **Lokasi** tersimpan ke metadata (UI ada, persist belum)

---

### F3 — Router Switcher (ganti dropdown)

**Trigger:** klik area header sidebar (`.mm-sidenav-header`) — bukan `<select>` native.

**Komponen:** modal/panel reuse pola `.mm-lang-dropdown__menu` (sudah ada dropdown bertema dengan search-like UX) atau panel fixed serupa `mm-confirm` tapi list mode.

**Isi panel:**
```
🔍 [form-control search]
─────────────────────
● Kos Coffee        ← aktif (check icon, class --active)
● Plampang Net
○ Cabang Timur (offline)
─────────────────────
+ Tambah router
📋 Semua router      → Router Hub
```

**Behavior:**
- Pilih router → reuse existing `connect(sessionId)` flow di `include/menu.php` (toast + skeleton + AJAX session persist)
- Tampilkan **display name** (hotspot name), slug secondary/muted (`.mm-sidenav-sub`)
- Offline router tetap selectable dengan warning toast
- Keyboard: Escape tutup; Enter pilih highlighted (nice-to-have P2)

**Fallback ≤3 router:** boleh tetap tampilkan opsi cepat di panel tanpa search bar.

**Acceptance criteria:**
- [x] Dropdown `<select class="mm-sidenav-session">` diganti trigger + panel
- [x] Display name tampil di sidebar header, bukan slug
- [x] Switch router tidak reload full page (reuse SPA flow existing)
- [x] Panel themed dark/light (reuse `body.theme-light` overrides)
- [ ] Keyboard Enter pilih highlighted (nice-to-have P2)

---

### F4 — Pemisahan Workspace vs Router Settings

**Perubahan navigasi sidebar (mode admin / tanpa session aktif):**

| Menu item | Route | Label baru |
|-----------|-------|--------------|
| Router Hub | `admin.php?id=routers` | Routers |
| Account Settings | `admin.php?id=sessions` | Pengaturan Akun |
| Upload Logo | `admin.php?id=uplogo` | Branding |
| (existing) | | |

**`settings/sessions.php` refactor:**
- **Hapus** daftar router dari halaman ini (pindah ke Router Hub)
- **Pertahankan** form admin (username, password, quick print QR)
- Optional: link "Kelola router →" ke Router Hub

**Acceptance criteria:**
- [x] Daftar router hanya di Router Hub
- [x] Account settings tidak menampilkan kartu router
- [x] Breadcrumb/teks navigasi konsisten

---

### F5 — Health Check & Status Cache (backend)

**Deskripsi:** Background ping router untuk status di Hub + sidebar.

**v1 (minimal):**
- Saat buka Router Hub: test connection per router (parallel, timeout 5s)
- Cache hasil di `$_SESSION['mm_router_status'][$slug]` TTL 60–120 detik
- Field: `online`, `last_seen`, `board_name`, `ros_version`, `active_users`, `total_users`, **`hdd_free`**, **`hdd_total`**, **`hdd_free_pct`**

**Threshold storage** (reuse pola dashboard `dashboard/aload.php`):
- `hdd_free_pct ≤ 10%` → status `critical` (chip danger / banner dashboard)
- `hdd_free_pct ≤ 25%` → status `warn` (chip warning di Hub card)
- `hdd_total = 0` atau tidak tersedia → abaikan (beberapa board tidak expose HDD)

**v2 (later):**
- Cron/queue background setiap 5 menit
- Push notification saat storage critical

**Acceptance criteria:**
- [x] Hub load ≤ 3s untuk 10 router (parallel requests + cache TTL 90s)
- [x] Offline router tidak block render router lain
- [x] Reuse `RouterService::getSystemResource()` untuk online check + HDD fields
- [x] Probe mengembalikan `hdd_free_pct`; chip warning tampil di kartu jika ≤ 25%
- [x] Dashboard router menampilkan banner `alert-warning` jika storage ≤ 10%
- [ ] v2: Cron background setiap 5 menit terpisah dari user visit (partial: `cron-tenant-maintenance.php`)
- [ ] v2: Push notification saat storage critical

---

### F6 — Plan Limit Display (placeholder)

**UI only v1:**
- [x] Teks di Router Hub header: `{used}/{limit} router`
- [x] Saat limit tercapai: disable tombol tambah + `alert alert-warning` inline
- [x] Limit dari constant PHP (`10` default) atau `MIKHMON_ROUTER_LIMIT`
- [ ] Limit per tier tenant dari DB (monetisasi — backlog P2)

**Out of scope v1:** payment gateway, upgrade flow.

---

### F7 — Mitigasi Storage MikroTik

**Deskripsi:** MikroTik dengan flash/HDD terbatas (mis. hAP lite 16 MB) rentan penuh karena MIKFAST menulis data ke router. Fitur ini mendeteksi, memperingatkan, dan mengurangi beban storage — tanpa mengubah arsitektur report existing di v1.

#### Konsumen storage di MIKFAST (existing)

| Sumber | Mekanisme | Dampak |
|--------|-----------|--------|
| Hotspot log | `ensureHotspotLoggingToDisk()` → `/system/logging` action `disk` | Log event ditulis ke flash |
| Laporan penjualan | Setiap transaksi → entry `/system/script` (comment `mikhmon`) | Menumpuk, tidak auto-purge |
| Scheduler | User profile expiry | Relatif kecil; dihapus setelah expire |

#### Layer 1 — Deteksi & peringatan (v1, P0)

Sudah tercakup di F1 + F5:
- HDD metric di Router Hub probe + kartu router
- Banner di dashboard router saat storage critical (≤ 10%)
- Storage summary di wizard test koneksi (F2)

#### Layer 2 — Kurangi tulis ke router (v1.5, P1)

**A. Conditional disk logging**

Saat ini `ensureHotspotLoggingToDisk()` dipanggil otomatis dari dashboard/log. Ubah menjadi:

```
if hdd_free_pct > 20 OR hdd_total unknown:
    ensureHotspotLoggingToDisk()   // action=disk
else:
    skip disk logging              // log tetap di memory RouterOS, hilang saat reboot
```

Implementasi: helper `RouterService::ensureHotspotLoggingSafe($resource)` — idempotent, tidak hapus rule existing.

**B. Batasi fetch log berat**

`hotspot/log_data.php` saat ini memanggil `getHotspotLogsAll()`. Mitigasi:
- Tambah parameter `?lines=` ke API RouterOS (`/log/print` dengan limit)
- Skip full fetch jika `hdd_free_pct ≤ 10%`; tampilkan pesan "Storage router penuh — log tidak tersedia"
- Pertahankan cache session existing (TTL 10s)

**C. Purge laporan penjualan lama (manual trigger v1.5)**

- Action baru di Router Settings atau Report: **"Hapus laporan > 90 hari"**
- Backend: hapus `/system/script` where `comment=mikhmon` AND `owner` older than threshold
- Konfirmasi via `mikhmon_confirm()` dengan jumlah entry yang akan dihapus
- v2: auto-purge terjadwal dari server SaaS

#### Layer 3 — Off-router storage (SaaS fase 2, P2)

Target arsitektur jangka panjang — router hanya runtime, bukan database:

```
Sekarang (partial):  transaksi → sync ke SQLite tenant (sync-reports, report-ingest)
                     hotspot log → batch DB + conditional disk logging di router
                     config router → data/tenants/{slug}/config.php + table routers

Target penuh:          transaksi → DB tenant only (tanpa script di MikroTik)
                       hotspot log → syslog/webhook stream → DB tenant
                       MikroTik → user, active, profile saja
```

**Status:** ✅ Core DB layer + cron sync/purge selesai (Fase 5). ⏸️ Full off-router (hapus tulis ke MikroTik) belum.

#### Rekomendasi hardware (onboarding copy)

Tampilkan di wizard F2 jika storage rendah:

| Kondisi | Pesan |
|---------|-------|
| `hdd_total ≤ 16 MB` | "Router ini punya storage sangat terbatas. Hapus laporan lama secara berkala atau pertimbangkan model dengan flash lebih besar." |
| `hdd_free_pct ≤ 25%` | "Storage hampir penuh. Hapus laporan lama di menu Report, atau hubungkan USB storage di router." |
| `hdd_free_pct ≤ 10%` | "Storage kritis — router bisa tidak stabil. Segera kosongkan laporan lama." |

#### Acceptance criteria

- [x] Probe health check mengembalikan `hdd_free`, `hdd_total`, `hdd_free_pct`
- [x] Hub card + wizard test koneksi menampilkan warning sesuai threshold
- [x] Dashboard router: banner warning jika storage ≤ 10%
- [x] `ensureHotspotLoggingSafe()` — skip disk logging jika `hdd_free_pct ≤ 20%` (v1.5)
- [x] Log page graceful degradation jika storage kritis (v1.5)
- [x] Action purge report lama dengan konfirmasi (v1.5)
- [ ] Auto-purge terjadwal per tier (partial: cron `--purge-days=90` global)

---

## 6. User Flows

### Flow 1 — First-time tenant

```
Login rival.mikfast.com
  → Router Hub (empty state)
  → Klik "Tambah Router Pertama"
  → Wizard L1: isi nama
  → Wizard L2: IP + credential → Test Koneksi ✓
  → Wizard L3: skip / confirm
  → Redirect Dashboard router baru
```

### Flow 2 — Switch router (daily)

```
Sedang di Dashboard Kos Coffee
  → Klik header sidebar
  → Panel switcher → pilih "Plampang Net"
  → Skeleton shimmer (existing mm-switching)
  → Toast "Connected to Plampang Net"
  → Dashboard Plampang load via AJAX
```

### Flow 3 — Router offline

```
Buka Router Hub
  → Kartu "Cabang Timur" chip Offline
  → Klik Buka
  → Dashboard tampil banner alert (reuse alert-warning):
     "Router offline. Terakhir online 2 jam lalu."
  → Tombol: [Coba reconnect] [Edit koneksi]
```

### Flow 4 — Storage router hampir penuh

```
Buka Router Hub
  → Kartu "Warung Pak Budi" chip ⚠ Storage 92% penuh
  → Klik Buka
  → Dashboard banner alert-warning:
     "Storage router hampir penuh (8% tersisa).
      Hapus laporan lama untuk mencegah router tidak stabil."
  → Tombol: [Hapus laporan lama] [Pelajari lebih lanjut]
  → (v1.5) Konfirmasi purge → toast sukses + storage meter refresh
```

---

## 7. Spesifikasi Teknis

### File baru (perkiraan)

| File | Fungsi | Status |
|------|--------|--------|
| `routers/hub.php` | View Router Hub | ✅ |
| `routers/add.php` | Wizard view (3 step) | ✅ |
| `routers/router-test.php` | AJAX test connection endpoint | ✅ |
| `routers/router-status.php` | AJAX batch status for hub | ✅ |
| `routers/add-save.php` | Wizard save handler | ✅ |
| `js/mikhmon/router-switcher.js` | Panel switcher logic | ✅ |
| `js/mikhmon/router-wizard.js` | Wizard step navigation | ✅ |
| `js/mikhmon/router-hub.js` | Hub status refresh + filter | ✅ |
| `include/router-hub.php` | Router list, probe, cache | ✅ |
| `include/router-storage-banner.php` | Dashboard storage banner | ✅ |
| `include/router-offline-banner.php` | Dashboard offline banner | ✅ |
| `process/purge-reports.php` | Purge old selling report scripts | ✅ |
| `process/sync-reports.php` | Sync reports to SQLite tenant | ✅ |
| `process/report-ingest.php` | Ingest report payload to DB | ✅ |
| `include/mikhmon-tenant.php` | Subdomain → tenant slug | ✅ |
| `include/mikhmon-bootstrap.php` | Per-tenant bootstrap | ✅ |
| `include/mikhmon-db.php` | SQLite per tenant | ✅ |
| `include/mikhmon-router-store.php` | Sync config ↔ DB routers | ✅ |
| `include/mikhmon-report-db.php` | Report/log DB layer | ✅ |
| `include/mikhmon-superadmin.php` | Super Admin auth + tenant CRUD | ✅ |
| `include/mikhmon-env.php` | Secure MIKHMON_* env reader | ✅ |
| `superadmin/login.php`, `superadmin/panel.php` | Super Admin UI | ✅ |
| `process/superadmin-tenant.php` | Super Admin AJAX actions | ✅ |
| `scripts/cron-tenant-maintenance.php` | Cron probe + sync + purge | ✅ |
| `DEPLOY.md` | Panduan deploy SaaS | ✅ |

### File dimodifikasi

| File | Perubahan | Status |
|------|-----------|--------|
| `admin.php` | Route hub/wizard/superadmin/cron; redirect login → routers | ✅ |
| `include/menu.php` | Sidebar header + switcher panel | ✅ |
| `settings/sessions.php` | Hapus router list; Account Settings | ✅ |
| `css/mikhmon-custom.css` | Class `mm-router-*`, wizard, switcher | ✅ |
| `include/router-hub.php` | HDD fields di probe + cache | ✅ |
| `lib/router/RouterService.php` | `ensureHotspotLoggingSafe()` | ✅ |
| `hotspot/log_data.php` | Limit log fetch; skip jika storage kritis | ✅ |
| `dashboard/aload.php` | Banner storage + safe logging | ✅ |
| `index.php` | Bootstrap multi-tenant | ✅ |

### Backward compatibility

- URL `?session=slug` **tidak berubah**
- `$data` array format config **tetap** di fase 1
- `connect()` JS function **tetap** dipakai switcher
- Admin route `id=sessions` tetap hidup (account settings)

### Multi-tenant (fase 2 — implementasi)

- ✅ Subdomain → tenant slug (`include/mikhmon-tenant.php`)
- ✅ Config per tenant: `data/tenants/{slug}/config.php` (+ migrasi legacy `include/config.php`)
- ✅ SQLite per tenant: `sales_reports`, `hotspot_logs`, `routers`, `tenant_meta`
- ✅ Super Admin panel: provisioning manual tenant di `admin.{domain}`
- ✅ Cron maintenance: probe, sync reports, auto-purge saat storage warn/critical
- ⏸️ Self-service signup tenant (belum)
- ⏸️ Billing / limit tier dari DB (belum)

### API endpoints (AJAX)

```
POST admin.php?id=router-test
  Body: { ip, user, pass }
  Response: { ok, board_name, ros_version, interfaces[], hdd_free, hdd_total, hdd_free_pct, storage_status }

GET admin.php?id=router-status&sessions[]=a&sessions[]=b
  Response: { routers: [{ slug, online, board_name, ros_version, active_users, total_users, last_seen, hdd_free, hdd_total, hdd_free_pct, storage_status }] }

POST admin.php?id=purge-reports  (v1.5)
  Body: { session, days: 90 }
  Response: { ok, removed_count, hdd_free_pct }
```

---

## 8. i18n — Key Baru

| Key | EN | ID |
|-----|----|----|
| `$_routers` | Routers | Router |
| `$_router_hub` | All Routers | Semua Router |
| `$_add_router` | Add Router | Tambah Router |
| `$_router_name` | Router Name | Nama Router |
| `$_test_connection` | Test Connection | Test Koneksi |
| `$_connection_ok` | Connected | Terhubung |
| `$_connection_failed` | Connection failed | Koneksi gagal |
| `$_router_offline` | Router offline | Router offline |
| `$_last_online` | Last online | Terakhir online |
| `$_account_settings` | Account Settings | Pengaturan Akun |
| `$_open_router` | Open | Buka |
| `$_router_limit` | {n}/{limit} routers | {n}/{limit} router |
| `$_empty_routers` | No routers connected yet | Belum ada router terhubung |
| `$_skip_setup` | Configure later | Atur nanti |
| `$_storage_warning` | Storage {pct}% full | Storage {pct}% penuh |
| `$_storage_critical` | Storage critically low ({pct}% free) | Storage kritis ({pct}% tersisa) |
| `$_storage_banner` | Router storage is almost full. Delete old reports to prevent instability. | Storage router hampir penuh. Hapus laporan lama agar router tetap stabil. |
| `$_purge_old_reports` | Delete reports older than {days} days | Hapus laporan > {days} hari |
| `$_purge_reports_confirm` | Delete {count} old report entries from this router? | Hapus {count} entri laporan lama dari router ini? |
| `$_log_unavailable_storage` | Log unavailable — router storage is full | Log tidak tersedia — storage router penuh |

Extend file lang existing (`include/lang/`) — jangan buat sistem i18n baru.

---

## 9. Fase Implementasi

### Fase 1 — Foundation (P0) — ✅ Selesai

1. Route + view Router Hub (`routers/hub.php`)
2. Redirect login → Router Hub
3. Kartu router dengan status (reuse `mm-chip`, `card`)
4. Hapus router list dari `sessions.php`
5. Rename label UI session → router

**Deliverable:** User bisa lihat & buka router dari Hub.

### Fase 2 — Add Router Wizard (P0) — ✅ Selesai

1. Wizard 3 langkah UI
2. Endpoint test connection
3. Integrasi save ke config per tenant
4. Empty state + CTA

**Deliverable:** User bisa tambah router end-to-end tanpa form lama.

### Fase 3 — Router Switcher (P0) — ✅ Selesai

1. Ganti `<select>` dengan panel
2. Display name di sidebar
3. Link ke Hub dari panel

**Deliverable:** Switch router scalable.

### Fase 4 — Polish & Storage (P1) — ✅ Selesai

1. Search/filter di Hub
2. Offline banner di dashboard
3. Plan limit placeholder
4. HDD metric di probe + chip warning di Hub card (F5/F7 Layer 1)
5. Storage banner di dashboard + wizard warning (F7 Layer 1)
6. i18n lengkap EN/ID (termasuk storage keys)
7. `graphify update .` setelah selesai

**Deliverable:** Hub polished + operator aware storage issues.

### Fase 4b — Storage mitigation (P1) — ✅ Selesai

1. Conditional `ensureHotspotLoggingSafe()` — skip disk logging jika storage ≤ 20%
2. Limit log fetch / graceful degradation di log page
3. Purge laporan lama (manual action + `process/purge-reports.php`)
4. Endpoint `purge-reports` + konfirmasi UI

**Deliverable:** Router kecil tidak cepat penuh; operator bisa cleanup tanpa Winbox.

### Fase 5 — Multi-tenant backend (P2) — ✅ Selesai (core)

1. ✅ Tenant resolver dari subdomain (`mikhmon_tenant_slug`, bootstrap)
2. ✅ DB storage routers + off-router report/log (`mikhmon-db`, `mikhmon-report-db`)
3. ✅ Background health check cron + auto-purge (`scripts/cron-tenant-maintenance.php`)

**Deliverable:** Satu codebase melayani banyak subdomain tenant dengan data terisolasi.

### Fase 5b — Super Admin panel (P2) — ✅ Selesai

1. ✅ Panel `admin.{domain}` — list/create/suspend/delete tenant
2. ✅ Auth via env PHP-FPM (`MIKHMON_SUPERADMIN_*`) — tidak di file web
3. ✅ Blok HTTP `/data/` + dokumentasi [DEPLOY.md](./DEPLOY.md)

**Deliverable:** Operator SaaS bisa provision tenant tanpa SSH manual.

### Fase 6 — Backlog SaaS polish — ✅ Selesai

1. ✅ Lokasi router disimpan di config (`@loc@`) + tampil di Hub
2. ✅ Router switcher: Arrow Up/Down + Enter
3. ✅ Push notif via `MIKHMON_NOTIFY_WEBHOOK` / Telegram (cron)
4. ✅ Limit **5 router** per tenant (`tenant_meta.router_limit`)
5. ✅ Off-router sales: profile on-login pakai `/tool fetch` → `report-ingest`
6. ✅ Log ingest: `admin.php?id=log-ingest` + cron sync

### Fase 7 — Backlog (belum)

| Item | Prioritas |
|------|-----------|
| Self-service tenant signup | P2 |
| Billing + limit tier variabel per paket | P2 |
| Email/WA notif native (selain webhook/Telegram) | P2 |
| i18n router/storage keys semua bahasa | P3 |

---

## 10. Metrik Sukses

| Metrik | Baseline | Target |
|--------|----------|--------|
| Waktu tambah router pertama | ~5–10 min (form manual) | ≤ 3 min |
| Klik untuk switch router | 2+ (settings → pilih) | 2 (header → pilih) |
| User comprehension "session" | Rendah | N/A (label "Router") |
| Mobile usability Hub | N/A | Lighthouse tap target pass |
| CSS baru | — | ≤ 80 baris `mm-*` total |
| Storage incident (router crash karena flash penuh) | Tidak terukur | ≤ 1 per tenant/quarter setelah Fase 4b |

---

## 11. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|----------|
| Test koneksi lambat untuk banyak router | Hub load lama | Parallel + timeout + cache session |
| Breaking SPA session switch | UX regression | Reuse `connect()` existing; test di mobile |
| Cloudflare cache (lihat UI-UX-AUDIT #H) | Deploy tidak terlihat | Pakai `mikhmon_asset_ver()` untuk JS/CSS baru |
| Scope creep ke billing/team | Delay | Strict non-goals v1 |
| Flash MikroTik penuh (log + report script) | Router unstable, config gagal save, reboot loop | F7 Layer 1–2: HDD metric + conditional logging + purge manual; Layer 3 off-router di fase SaaS DB |
| `getHotspotLogsAll()` berat di router kecil | API timeout, Hub false-offline | Limit lines + cache agresif + skip fetch jika storage ≤ 10% |

---

## 12. Open Questions

| # | Pertanyaan | Keputusan sementara / status |
|---|------------|------------------------------|
| 1 | **Limit router default** tier gratis — berapa? | Default **5** per tenant (`tenant_meta.router_limit`); override env `MIKHMON_ROUTER_LIMIT`. |
| 2 | **Slug rename** router — boleh rename setelah dibuat? | Code support via settings; UX guided belum — backlog P3 |
| 3 | **Lokasi field** — simpan di mana? | ✅ Config index `@loc@` per router slug. |
| 4 | **Subdomain provisioning** | ✅ Manual via **Super Admin panel** (Fase 5b). Self-service signup belum. |
| 5 | **Retention default purge report** | Cron global **90 hari** (`--purge-days=90`). Per-tier belum. |
| 6 | **Threshold storage** konfigurasi per tenant? | Hardcoded 25%/10%/20% di `router-hub.php`. Per-tenant config belum. |

---

## 13. Lampiran — Mapping Komponen Existing

| Kebutuhan UI | Gunakan existing | Jangan buat |
|--------------|------------------|-------------|
| Kartu router | `.card`, `.card-header`, `.card-body` | Card component React/Vue |
| Status online | `.mm-chip`, `.mm-chip--ok`, `.mm-chip--muted` | Custom badge CSS |
| Loading | `.mm-loaderbar` | Spinner library |
| Toast feedback | `#mmToast`, `mikhmon_toast()` | alert() native |
| Konfirmasi hapus | `mikhmon_confirm()` | confirm() native |
| Form input | `.form-control`, `.table` form layout | New form framework |
| Tombol primary | `.btn`, `.bg-primary` / theme default | Custom button component |
| Tombol ghost | `.mm-btn-ghost` | — |
| Tombol hapus | `.mm-action-btn--danger` atau `.btn.bg-danger` | — |
| Grid layout | `.row`, `.col-4`, `.col-6`, `.col-12` | CSS Grid custom layout |
| Modal/panel | `.mm-confirm` pattern / `.mm-lang-dropdown__menu` | Bootstrap modal import |
| Page transition | `#mmPageSkeleton`, `body.mm-switching` | Full page reload |
| Header halaman | `.mm-dashheader` pattern | — |
| Theme support | `body.theme-light` overrides | Separate light stylesheet |

---

*Dokumen ini menjadi acuan implementasi fase Router Hub SaaS. **Fase 1–5b selesai** per snapshot 2026-08-02; lihat §Status Implementasi untuk backlog. Setiap PR baru harus referensikan section fitur yang diimplementasi dan patuhi `.cursor/rules/mikfast-design.mdc`.*
