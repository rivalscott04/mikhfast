### MIKFAST V3 (Hotspot Voucher Manager)

MIKFAST v3 adalah aplikasi web untuk membantu admin hotspot MikroTik (voucher, user, report, monitoring) lewat RouterOS API.

Repo ini adalah fork/pengembangan dari project upstream (MIKHMON/MIKFAST v3) dengan fokus utama: **lebih nyaman dipakai harian**, **lebih rapi di layer API**, dan **lebih aman dipakai di RouterOS v6 & v7**.

#### Kelebihan dibanding default (upstream)
- **Redesign UI/UX (by Rival)**: sidebar lebih simple (2-level), navigasi lebih cepat, dan komponen UI lebih konsisten untuk workflow voucher.
- **Kompatibilitas RouterOS v6/v7 (Adapter Layer)**: komunikasi API dipusatkan lewat `lib/router/RouterService.php` yang memilih `Ros6Adapter` atau `Ros7Adapter`, jadi module/UI tidak perlu “tau” detail perbedaan versi.
- **Fondasi auto-detect major version**: saat ini adapter dipilih via parameter `rosMajorVersion` saat membuat `RouterService` (siap dikembangkan ke auto-detect capability).
- **Query lebih ringan & stabil**: beberapa request sudah memakai pembatasan field (proplist) sehingga payload lebih kecil, parsing lebih cepat, dan mengurangi potensi error akibat field berlebih.
- **Output lebih konsisten**: beberapa hasil query dinormalisasi (mis. `getIdentity()` selalu `["name" => "..."]`) supaya pemakaian di UI lebih mudah.
- **Navigasi SPA/AJAX (lebih responsif)**: request yang aman (GET navigasi & beberapa POST) bisa dikembalikan sebagai JSON wrapper lalu diganti di `.wrapper` tanpa full reload (fallback tetap ke navigasi biasa jika gagal).
- **Branding MIKFAST**: logo/ikon menggunakan SVG (`img/mikfast.svg`) untuk tampilan lebih modern dan tajam (retina-ready).

#### Fokus pemakaian (workflow voucher)
- **Users**: list, tambah user, generate user/voucher.
- **Vouchers by Profile**: lihat dan generate voucher berdasarkan profile.
- **Quick Print**: akses cepat untuk cetak voucher.
- **Monitoring**: hotspot active, log, dan tools pendukung (hosts, cookies, ip binding, dhcp leases).

#### Catatan singkat (biar jelas)
- **RouterOS v6/v7**: versi firmware MikroTik. Kadang endpoint/field API beda tipis, jadi aplikasi lama bisa “ngadat” kalau dipakai di versi baru.
- **Adapter**: “penerjemah” yang bikin kode aplikasi tetap rapi — yang beda-beda ditangani di satu tempat.

### Perubahan utama (fork ini)
- **Redesign (by Rival)**: pembaruan tampilan/UX pada fork ini.
- **Refactor (RouterOS API layer)**: memusatkan komunikasi RouterOS lewat `RouterService` + `RouterAdapterInterface` (module/UI tidak perlu menangani detail perbedaan versi).
- **Compatibility (ROS v6/v7)**: menambahkan `Ros6Adapter` dan `Ros7Adapter` sebagai pondasi kompatibilitas RouterOS v6/v7.
- **Performance/Optimization**: optimasi beberapa request RouterOS dengan pembatasan field (proplist) supaya payload lebih kecil dan lebih stabil.

#### Credit / Penghargaan
- **Author asli (upstream) v3**: **Laksa19**
  - Website: `https://laksa19.github.io/`
  - Repo upstream (referensi): `https://github.com/laksa19/`

#### Branding / UI
- **Redesign & improvements**: Rival

---

## Deploy & Permission File (WAJIB BACA)

> **Deploy SaaS multi-tenant (subdomain + Super Admin):** baca **[DEPLOY.md](./DEPLOY.md)** — panduan lengkap step-by-step untuk pemula (DNS wildcard, Nginx, env PHP-FPM, buat tenant).

### Install otomatis (recommended)

Satu perintah — clone, protect config, permission, siap pakai UI:

```bash
# Fresh install ke /var/www/mikhfast
curl -fsSL https://raw.githubusercontent.com/rivalscott04/mikhfast/master/scripts/install-mikhfast.sh | sudo bash

# Custom path
curl -fsSL https://raw.githubusercontent.com/rivalscott04/mikhfast/master/scripts/install-mikhfast.sh | sudo bash -s -- --dir /var/www/mikhfast
```

Atau kalau sudah clone manual:

```bash
cd /var/www/mikhfast
sudo bash scripts/install-mikhfast.sh
```

Update install (pull + permission, **config router tidak ditimpa**):

```bash
cd /var/www/mikhfast
sudo bash scripts/install-mikhfast.sh --update
```

Installer akan:
1. `git clone` / `git pull` repo
2. Backup & restore `include/config.php` saat update
3. `git rm --cached` untuk `config.php` & `quickbt.php` (data live aman dari git pull)
4. Buat `config.php` dari `config.php.example` kalau belum ada
5. Jalankan `setup-permissions.sh` + `check-persistence.sh`

Setelah selesai, user cukup buka **Admin UI** → login `mikhmon`/`1234` → **Add Router**.

---

### Checklist deploy — folder yang harus ada

```
mikhfast/
├── include/          ← WAJIB (18 file PHP). Kosong = situs 500
├── img/              ← WAJIB (mikfast.svg, favicon.png). Kosong = logo broken
├── voucher/          ← WAJIB untuk generate & print voucher
├── admin.php
├── index.php
└── ... (folder css, js, lib, hotspot, dll)
```

Clone/pull dari repo:

```bash
cd /var/www
git clone https://github.com/rivalscott04/mikhfast.git mikhfast
# atau kalau sudah ada (config router TIDAK ikut git pull):
cd /var/www/mikhfast && sudo bash scripts/deploy.sh
# deploy = safe-pull + bun run build + permission
```

### Auto deploy (push → server)

Setiap **push ke `master`**, GitHub Actions SSH ke VPS dan jalankan `scripts/deploy.sh` (pull aman + `bun run build`).

**Setup sekali di GitHub** → repo → Settings → Secrets and variables → Actions:

| Secret | Contoh |
|--------|--------|
| `DEPLOY_HOST` | IP VPS atau domain |
| `DEPLOY_USER` | `root` |
| `DEPLOY_SSH_KEY` | Private key SSH (paste full `-----BEGIN OPENSSH...`) |
| `DEPLOY_PORT` | `22` *(opsional)* |
| `DEPLOY_PATH` | `/var/www/mikhfast` *(opsional, default path deploy)* |

**Setup sekali di VPS:**

```bash
# Install bun
curl -fsSL https://bun.sh/install | bash
source ~/.bashrc   # atau login ulang

# Deploy manual (sama seperti CI)
cd /var/www/mikhfast && sudo bash scripts/deploy.sh
```

Pastikan user SSH bisa `sudo bash scripts/deploy.sh` tanpa password, atau jalankan Actions sebagai user yang punya akses write ke `/var/www/mikhfast`.

Debug JS per-modul (tanpa bundle): tambah `&debug_js=1` di URL.

---

### Permission per fitur

| Fitur | File/folder yang perlu **writable** | Catatan |
|-------|--------------------------------------|---------|
| **Login / Admin Settings → Save** | `include/config.php`, `include/quickbt.php` | Simpan username, password admin, QR setting |
| **Add Router** | `include/config.php` | Append baris session MikroTik baru |
| **Edit Router Settings** | `include/config.php` | Update IP, user, password router |
| **Hapus Router (Delete session)** | `include/config.php` | Hapus baris session dari config |
| **Add User** | — *(tidak tulis file lokal)* | Langsung ke MikroTik API |
| **Generate User / Voucher** | `voucher/temp.php`, folder `voucher/` | Simpan metadata untuk print voucher |
| **Print / Quick Print voucher** | `voucher/temp.php` *(read)* | Baca data generate terakhir |
| **Voucher Editor → Save template** | `voucher/template.php`, `voucher/template-thermal.php`, `voucher/template-small.php` | Edit layout voucher |
| **Upload Logo session** | folder `img/`, `img/logo-{session}.png` | Butuh PHP GD untuk JPG/WEBP |
| **Ganti bahasa** | `include/lang.php` | Ditimpa saat pilih language |
| **Ganti tema dark/light** | `include/theme.php` | Ditimpa saat toggle theme |

---

### Tabel permission lengkap

| Path | chmod | Owner | Writable? | Keterangan |
|------|-------|-------|-----------|------------|
| `include/` | `755` | www-data | folder | PHP harus bisa baca & masuk folder |
| **`include/config.php`** | **`664`** | www-data | **Ya** | **Paling penting** — semua setting router & admin |
| **`include/quickbt.php`** | **`664`** | www-data | **Ya** | Setting Quick Print QR |
| `include/lang.php` | `664` | www-data | Ya | Ganti bahasa |
| `include/theme.php` | `664` | www-data | Ya | Ganti tema |
| `include/*.php` (sisanya) | `644` | www-data | Tidak | ajax.php, menu.php, readcfg.php, dll — cukup read |
| **`img/`** | **`775`** | www-data | **Ya** | Upload logo |
| `img/mikfast.svg` | `644` | www-data | Tidak | **Harus ada** — logo navbar/sidebar |
| `img/favicon.png` | `644` | www-data | Tidak | **Harus ada** — favicon browser |
| `img/logo-{session}.png` | `664` | www-data | Ya | Dibuat otomatis saat upload logo |
| **`voucher/`** | **`775`** | www-data | **Ya** | Generate user & template |
| **`voucher/temp.php`** | **`664`** | www-data | **Ya** | Metadata generate voucher |
| `voucher/template.php` | `664` | www-data | Ya | Template voucher default |
| `voucher/template-thermal.php` | `664` | www-data | Ya | Template thermal |
| `voucher/template-small.php` | `664` | www-data | Ya | Template small |

---

### Setup permission (script otomatis)

Jalankan di server (dari root folder app):

```bash
cd /var/www/mikhfast
sudo bash scripts/setup-permissions.sh
```

Path custom:

```bash
sudo MIKFAST_WEB_USER=www-data bash scripts/setup-permissions.sh /var/www/mikhfast
```

Script akan:
- deteksi user PHP-FPM (`www-data` / `nginx` / `apache`)
- buat `include/config.php` default kalau hilang/kosong
- buat `include/quickbt.php` dan `voucher/temp.php` kalau belum ada
- set owner + chmod semua file yang perlu writable
- verifikasi akhir (config, voucher, img)

Manual (kalau tidak pakai script):

```bash
APP=/var/www/mikhfast
WEB=www-data

cd "$APP"

# 1. Pastikan file dari repo ada (bukan folder kosong)
test -f include/config.php || echo "ERROR: include/ kosong — git pull dulu!"
test -f img/mikfast.svg    || echo "ERROR: img/ kosong — git pull dulu!"

# 2. Owner
chown -R $WEB:$WEB include/ img/ voucher/

# 3. Folder
chmod 755 include/
chmod 775 img/ voucher/

# 4. include — writable
chmod 664 include/config.php include/lang.php include/theme.php
touch include/quickbt.php
chmod 664 include/quickbt.php

# 5. include — read-only
find include/ -maxdepth 1 -name '*.php' \
  ! -name 'config.php' ! -name 'quickbt.php' \
  ! -name 'lang.php' ! -name 'theme.php' \
  -exec chmod 644 {} \;

# 6. voucher — generate user & template editor
touch voucher/temp.php
chmod 664 voucher/temp.php
find voucher/ -maxdepth 1 -name 'template*.php' -exec chmod 664 {} \; 2>/dev/null

# 7. img — asset statis
chmod 644 img/mikfast.svg img/favicon.png 2>/dev/null

# 8. Verifikasi
echo "=== Cek permission ==="
sudo -u $WEB test -r include/config.php && echo "[OK] config readable" || echo "[GAGAL] config readable"
sudo -u $WEB test -w include/config.php && echo "[OK] config writable" || echo "[GAGAL] config writable"
sudo -u $WEB test -w voucher/temp.php   && echo "[OK] voucher/temp writable" || echo "[GAGAL] voucher/temp writable"
sudo -u $WEB test -w img/               && echo "[OK] img/ writable" || echo "[GAGAL] img/ writable"
sudo -u $WEB test -r img/mikfast.svg    && echo "[OK] mikfast.svg ada" || echo "[GAGAL] mikfast.svg tidak ada"
```

---

### `include/config.php` default (kalau hilang/kosong)

```php
<?php 
if(substr($_SERVER["REQUEST_URI"], -10) == "config.php"){header("Location:./");}; 
$data['mikhmon'] = array ('1'=>'mikhmon<|<mikhmon','mikhmon>|>aWNlbA==','qrbt<|<disable');
```

Login default: **`mikhmon`** / **`1234`**  
Setelah login → **Admin Settings → Add Router** → isi ulang session MikroTik.

---

### Gambar / logo not found (broken image)

**Penyebab:** file `img/mikfast.svg` **tidak ada di server** (404), bukan masalah permission.

Cek:
```bash
curl -I https://domain-kamu/img/mikfast.svg
# Harus HTTP 200, bukan 404
```

Fix:
```bash
cd /var/www/mikhfast
git checkout origin/master -- img/
ls -la img/mikfast.svg img/favicon.png
chmod 644 img/mikfast.svg img/favicon.png
```

Logo per session (`img/logo-plampang.png` dll) opsional. Kalau belum upload, UI pakai fallback `mikfast.svg`.

---

### Data hilang setelah restart VPS?

**Restart VPS tidak menjalankan kode hapus data.** Kalau setelah reboot router/config/include hilang, penyebabnya di **infrastruktur deploy**, bukan fitur app.

| Penyebab | Penjelasan |
|----------|------------|
| **`git pull` / `git reset --hard` saat boot** | `include/config.php` berisi semua data router. Kalau masih di-track git, pull/reset **timpa** config ke versi repo (cuma admin default) |
| **Docker tanpa volume bind ke host** | File cuma di dalam container. Container recreate = folder kosong |
| **Deploy ke tmpfs / RAM disk** | `/var/www` di tmpfs → hilang saat reboot |
| **Upload/rsync folder kosong** | FTP upload folder `include/` kosong menimpa yang ada |
| **Script auto-deploy saat boot** | systemd/cron yang `git reset` atau `rsync --delete` |

Diagnose di server:

```bash
sudo bash scripts/check-persistence.sh /var/www/mikhfast
```

**PENTING — jangan commit data live:**

`include/config.php` sekarang di-`.gitignore`. Template ada di `include/config.php.example`.

Setelah pull update terbaru, jalankan sekali:

```bash
git rm --cached include/config.php include/quickbt.php 2>/dev/null || true
```

Backup rutin data router:

```bash
cp include/config.php include/config.php.bak
# atau tar:
tar czf mikhfast-backup-$(date +%F).tar.gz include/config.php img/logo-*.png voucher/temp.php
```

#### Bug tulis config di app (sudah diperbaiki)

| Bug | Dampak | Status |
|-----|--------|--------|
| Delete session: `fopen(w)` truncate dulu | `config.php` bisa kosong | ✅ Fixed |
| Save admin: tulis tanpa validasi | Config corrupt/kosong kalau read gagal | ✅ Fixed |
| Save router: `preg_replace` tanpa guard | Config bisa invalid | ✅ Fixed |
| `config.php` di-track git | `git pull` timpa data router | ✅ Fixed (.gitignore) |

Semua write ke `config.php` sekarang lewat `include/config-write.php`:
- validasi isi sebelum tulis
- auto backup ke `config.php.bak`
- tolak write kalau config invalid/kosong

---

### Troubleshooting

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| HTTP 500 | `include/` kosong atau `config.php` rusak | `git pull` / restore `include/` |
| Save admin/router gagal | `config.php` not writable | `chmod 664` + `chown www-data` |
| Router list kosong | Belum add router atau config kosong | Add Router + cek isi `config.php` |
| Generate user gagal / print error | `voucher/temp.php` not writable | `chmod 664 voucher/temp.php` |
| Logo upload gagal | `img/` not writable | `chmod 775 img/` |
| Ikon MIKFAST broken | `img/mikfast.svg` tidak ke-deploy | Upload folder `img/` dari repo |
| Upload JPG/WEBP gagal | PHP GD tidak aktif | `apt install php-gd` + restart php-fpm |

### PHP extension (upload logo JPG/WEBP)

```bash
sudo apt install php-gd
sudo systemctl restart php8.3-fpm   # sesuaikan versi PHP
```

---
