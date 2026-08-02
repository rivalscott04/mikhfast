# Panduan Deploy MIKFAST (SaaS Multi-Tenant)

Panduan ini untuk **pemula** — langkah demi langkah, dari nol sampai bisa dipakai banyak pelanggan (tenant) lewat subdomain.

---

## Apa yang kamu bangun?

Bayangkan **satu aplikasi** di satu server, tapi bisa melayani banyak pelanggan:

| Alamat | Fungsi |
|--------|--------|
| `admin.mikfast.com` | **Panel Super Admin** — buat/hapus/suspend tenant |
| `kos.mikfast.com` | Tenant "kos" — admin kos login & kelola router |
| `warnet.mikfast.com` | Tenant "warnet" — admin warnet login & kelola router |

**Satu kode, satu upload.** Data tiap tenant tersimpan terpisah di folder `data/tenants/{slug}/`. Tidak perlu upload ulang per pelanggan.

```
                    ┌─────────────────────────┐
   *.mikfast.com ──►│  VPS (Nginx + PHP-FPM)  │
                    │  /var/www/mikhfast      │
                    └───────────┬─────────────┘
                                │
              ┌─────────────────┼─────────────────┐
              ▼                 ▼                 ▼
     data/tenants/kos/   data/tenants/warnet/   ...
     config + sqlite     config + sqlite
```

---

## Yang dibutuhkan sebelum mulai

Centang dulu — kalau ada yang belum, selesaikan dulu:

- [ ] **VPS** (Ubuntu/Debian, min. 1 GB RAM, 20 GB disk)
- [ ] **Domain** (contoh: `mikfast.com`)
- [ ] **Akses SSH** ke VPS (user root atau sudo)
- [ ] **DNS wildcard** — lihat Langkah 2
- [ ] **Nginx** + **PHP 8.x** + **PHP-FPM** + ekstensi `pdo_sqlite`, `gd`

> **Wildcard DNS** = satu setting DNS supaya **semua** subdomain (`*.mikfast.com`) mengarah ke IP server yang sama.

---

## Langkah 1 — Install aplikasi di server

### Opsi A: Install otomatis (disarankan)

SSH ke VPS, lalu:

```bash
curl -fsSL https://raw.githubusercontent.com/rivalscott04/mikhfast/master/scripts/install-mikhfast.sh | sudo bash
```

Default path: `/var/www/mikhfast`

Path custom:

```bash
curl -fsSL https://raw.githubusercontent.com/rivalscott04/mikhfast/master/scripts/install-mikhfast.sh | sudo bash -s -- --dir /var/www/mikhfast
```

### Opsi B: Clone manual

```bash
cd /var/www
sudo git clone https://github.com/rivalscott04/mikhfast.git mikhfast
cd mikhfast
sudo bash scripts/install-mikhfast.sh --skip-pull
```

### Cek hasil install

```bash
ls /var/www/mikhfast/admin.php
ls /var/www/mikhfast/data/tenants
```

Kalau kedua file/folder ada → lanjut Langkah 2.

---

## Langkah 2 — Atur DNS (wildcard)

Di panel DNS domain kamu (Cloudflare, Niagahoster, dll), tambahkan:

| Tipe | Nama | Isi | TTL |
|------|------|-----|-----|
| **A** | `@` | IP VPS kamu | Auto |
| **A** | `*` | IP VPS kamu | Auto |
| **A** | `admin` | IP VPS kamu | Auto |

Contoh: IP VPS = `203.0.113.10`

- `mikfast.com` → `203.0.113.10`
- `*.mikfast.com` → `203.0.113.10` (wildcard — penting!)
- `admin.mikfast.com` → `203.0.113.10`

Tunggu 5–30 menit (propagasi DNS), lalu cek:

```bash
ping admin.mikfast.com
ping tes123.mikfast.com
```

Keduanya harus resolve ke IP VPS yang sama.

---

## Langkah 3 — Konfigurasi Nginx

Buat file site config:

```bash
sudo nano /etc/nginx/sites-available/mikfast
```

Isi (ganti `mikfast.com` dan path PHP socket sesuai server):

```nginx
server {
    listen 80;
    server_name mikfast.com *.mikfast.com;

    root /var/www/mikhfast;
    index index.php;

    # PENTING: folder data tenant TIDAK boleh diakses publik
    location ^~ /data/ {
        deny all;
        return 403;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

Aktifkan & reload:

```bash
sudo ln -sf /etc/nginx/sites-available/mikfast /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### HTTPS (Let's Encrypt) — disarankan

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d mikfast.com -d "*.mikfast.com"
```

> Certbot wildcard butuh DNS challenge. Kalau sulit, minimal HTTPS untuk `admin.mikfast.com` dan subdomain tenant yang sudah dipakai.

---

## Langkah 4 — Secret di environment (PHP-FPM)

Password super-admin **tidak** disimpan di file web. Set di **PHP-FPM pool** saja.

Edit pool (sesuaikan versi PHP):

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Tambahkan di bagian bawah:

```ini
; Super Admin (panel admin.mikfast.com)
env[MIKHMON_SUPERADMIN_USER] = superadmin
env[MIKHMON_SUPERADMIN_PASS] = GantiPasswordKuat123!

; Domain tenant (tanpa subdomain)
env[MIKHMON_BASE_DOMAIN] = mikfast.com

; Token cron (string acak, untuk maintenance otomatis)
env[MIKHMON_CRON_TOKEN] = ganti-dengan-string-acak-panjang

; --- Fase 6: SaaS polish (opsional) ---

; Limit router per tenant (default 5; bisa override per tenant di Super Admin meta)
env[MIKHMON_ROUTER_LIMIT] = 5

; Token ingest report/log dari MikroTik (wajib jika off-router aktif)
env[MIKHMON_INGEST_TOKEN] = ganti-dengan-string-acak-panjang

; Off-router: profile hotspot kirim sales via HTTPS fetch, bukan script di router (1=on, 0=off)
env[MIKHMON_OFF_ROUTER] = 1

; Push notif storage/offline (webhook JSON POST)
env[MIKHMON_NOTIFY_WEBHOOK] = https://hooks.example.com/mikhmon-alerts

; Opsional: Telegram (bot token + chat id)
; env[MIKHMON_NOTIFY_TELEGRAM_BOT] = 123456:ABC...
; env[MIKHMON_NOTIFY_TELEGRAM_CHAT] = -1001234567890
; env[MIKHMON_NOTIFY_COOLDOWN] = 3600

; Base URL tenant jika auto-detect gagal (untuk /tool fetch di MikroTik)
; env[MIKHMON_INGEST_BASE_URL] = https://tenant.mikfast.com
```

Generate token acak:

```bash
openssl rand -hex 32
```

**Opsi lebih aman** — pakai hash password (tanpa plain text di config):

```bash
php -r "echo password_hash('GantiPasswordKuat123!', PASSWORD_DEFAULT) . PHP_EOL;"
```

Lalu di pool:

```ini
env[MIKHMON_SUPERADMIN_USER] = superadmin
env[MIKHMON_SUPERADMIN_PASS_HASH] = $2y$10$...hasil-hash...
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.3-fpm
```

### Jangan lakukan ini

| ❌ Jangan | Kenapa |
|----------|--------|
| Taruh `.env` di folder web | Bisa kebaca publik kalau nginx salah config |
| Commit password ke Git | Repo bisa bocor |
| Pakai `phpinfo()` di production | Menampilkan semua env ke browser |

---

## Langkah 5 — Permission folder

Jalankan sekali (atai ulang setelah update):

```bash
cd /var/www/mikhfast
sudo bash scripts/setup-permissions.sh
```

Script ini memastikan:

- `data/tenants/` bisa ditulis PHP (config + database SQLite per tenant)
- `img/`, `voucher/` bisa ditulis untuk upload logo & generate voucher
- File PHP inti tetap read-only

Verifikasi:

```bash
sudo bash scripts/check-persistence.sh /var/www/mikhfast
```

---

## Langkah 6 — Login Super Admin & buat tenant

1. Buka browser: **`https://admin.mikfast.com/admin.php?id=superadmin`**
2. Login dengan user/password dari Langkah 4
3. Di form **Create Tenant**, isi:
   - **Slug** — nama subdomain (huruf kecil, contoh: `kos`, `warnet-jaya`)
   - **Label** — nama tampilan (opsional, contoh: "Kos Coffee")
   - **Admin** — username admin tenant
   - **Password** — password admin tenant
4. Klik **Create**

Tenant siap di: **`https://kos.mikfast.com/admin.php?id=login`**

### Apa yang terjadi di belakang layar?

Super Admin membuat folder:

```
data/tenants/kos/
├── config.php      ← login admin tenant + daftar router
├── meta.json       ← status (active/suspended), label
└── mikfast.sqlite  ← laporan penjualan & log (jika SQLite aktif)
```

---

## Langkah 7 — Tenant pakai aplikasi (admin pelanggan)

Alur untuk admin tenant (misal pemilik kos):

1. Buka `https://kos.mikfast.com/admin.php?id=login`
2. Login dengan admin yang dibuat Super Admin
3. **Add Router** → isi IP, user, password API MikroTik
4. Kelola voucher, user hotspot, laporan — seperti MIKFAST biasa

Setiap tenant **terisolasi** — tenant A tidak bisa lihat data tenant B.

---

## Langkah 8 — Cron maintenance (opsional tapi disarankan)

Cron menjaga router tetap ter-monitor dan membersihkan laporan lama kalau storage penuh.

### Via crontab (disarankan)

```bash
sudo crontab -e
```

Tambahkan (jalan setiap 15 menit):

```cron
*/15 * * * * cd /var/www/mikhfast && /usr/bin/php scripts/cron-tenant-maintenance.php --purge-days=90 >> /var/log/mikfast-cron.log 2>&1
```

### Via HTTP (alternatif)

```
GET https://admin.mikfast.com/admin.php?id=tenant-cron&token=TOKEN_DARI_ENV&purge_days=90
```

Token harus sama dengan `MIKHMON_CRON_TOKEN` di PHP-FPM.

Cron juga menjalankan: probe router offline, alert storage (jika `MIKHMON_NOTIFY_WEBHOOK` / Telegram diset), dan sync hotspot log ke SQLite.

### Off-router & ingest (Fase 6)

Jika `MIKHMON_OFF_ROUTER=1` (default saat SQLite aktif):

- Profile hotspot **tidak** menulis script sales ke MikroTik — on-login memanggil `/tool fetch` ke `https://{tenant}/admin.php?id=report-ingest`
- Router harus bisa HTTPS ke subdomain tenant
- Set `MIKHMON_INGEST_TOKEN` yang sama di env server; token dikirim sebagai query `token=...`
- Log hotspot bisa di-push ke `admin.php?id=log-ingest` (webhook) atau di-pull cron dari router

---

## Update aplikasi (setelah ada perubahan di GitHub)

```bash
cd /var/www/mikhfast
sudo bash scripts/deploy.sh
```

Deploy script akan:

1. Git pull aman (data tenant **tidak** ikut tertimpa)
2. Set permission ulang
3. Build frontend (jika `bun` terinstall)

Atau pakai installer update:

```bash
cd /var/www/mikhfast
sudo bash scripts/install-mikhfast.sh --update
```

---

## Backup data tenant

Data penting ada di `data/tenants/`. Backup rutin:

```bash
tar czf mikfast-backup-$(date +%F).tar.gz -C /var/www/mikhfast data/tenants
```

Simpan file `.tar.gz` di luar server (Google Drive, S3, dll).

Restore:

```bash
cd /var/www/mikhfast
sudo tar xzf mikfast-backup-2026-08-02.tar.gz
sudo bash scripts/setup-permissions.sh
```

---

## Checklist keamanan sebelum go-live

- [ ] HTTPS aktif di semua subdomain
- [ ] `location ^~ /data/` deny di Nginx
- [ ] Password super-admin kuat & hanya di PHP-FPM env
- [ ] `MIKHMON_CRON_TOKEN` acak & panjang
- [ ] Firewall VPS: buka 80, 443, 22 saja
- [ ] SSH pakai key, bukan password root
- [ ] Backup `data/tenants/` terjadwal

---

## Troubleshooting (FAQ)

### Super Admin: "credentials not configured"

Env belum masuk PHP-FPM. Cek:

```bash
grep MIKHMON /etc/php/8.3/fpm/pool.d/www.conf
sudo systemctl restart php8.3-fpm
```

### Super Admin: halaman login muncul tapi tenant subdomain error 404

- Nginx belum `server_name *.mikfast.com`
- DNS wildcard belum propagate — tunggu atau cek di panel DNS

### Tenant: "This workspace is suspended"

Super Admin men-suspend tenant. Buka panel super-admin → unsuspend.

### HTTP 500 setelah deploy

```bash
cd /var/www/mikhfast
sudo bash scripts/setup-permissions.sh
sudo tail -30 /var/log/nginx/error.log
```

### Logo / ikon broken

```bash
ls -la /var/www/mikhfast/img/mikfast.svg
# Harus ada. Kalau tidak:
cd /var/www/mikhfast && git checkout origin/master -- img/
```

### Data tenant hilang setelah reboot VPS

Bukan karena reboot — biasanya deploy script atau Docker tanpa volume. Diagnose:

```bash
sudo bash scripts/check-persistence.sh /var/www/mikhfast
```

### Cloudflare: UI lama setelah update

Pastikan deploy sudah jalan. MIKFAST pakai cache-buster otomatis (`?v=filemtime`). Kalau masih stale, purge cache Cloudflare untuk domain tersebut.

---

## Ringkasan alur deploy

```
1. Install app di VPS          → scripts/install-mikhfast.sh
2. DNS wildcard *.domain       → panel DNS
3. Nginx + blok /data/         → sites-available
4. Env di PHP-FPM              → superadmin + cron + base domain
5. Permission                  → setup-permissions.sh
6. Login admin.domain          → buat tenant
7. tenant.domain               → admin pelanggan add router
8. Cron (opsional)             → cron-tenant-maintenance.php
9. Backup rutin                → tar data/tenants/
```

---

## Butuh bantuan?

- Permission & config lama: lihat juga [README.md — Deploy & Permission](./README.md#deploy--permission-file-wajib-baca)
- Spesifikasi fitur SaaS: [PRD-SAAS-ROUTER-HUB.md](./PRD-SAAS-ROUTER-HUB.md)
