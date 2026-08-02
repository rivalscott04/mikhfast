# Audit UI/UX — MIKFAST (mikhmonnew)

Tanggal: 2026-08-02
Metode: audit dua tahap. Tahap 1 (bagian bawah dokumen) adalah code review statis terhadap markup PHP + CSS di repo lokal. Tahap 2 (bagian atas, di bawah ini) adalah pengujian langsung via browser (Chrome DevTools automation, klik & resize sungguhan) terhadap server produksi **mikh.rivaldev.site**, login sebagai `rival`, memakai data hotspot nyata (session `kos` / `goa.io`, 170 user, 7 active).

> **Catatan penting:** server produksi ternyata menjalankan kode yang **lebih baru dari repo lokal** — ditemukan blok CSS khusus tablet (`@media screen and (min-width: 751px) and (max-width: 1024px)` di `mikhmon-custom.css`) yang sama sekali tidak ada di checkout lokal. Sebagian rekomendasi Tahap 1 (breakpoint tablet, KPI 2-kolom) sepertinya **sudah pernah dikerjakan di server**, tapi implementasinya menabrak kode JS lama yang belum ikut di-update — lihat temuan #A di bawah. Disarankan tarik kode dari server produksi kembali ke repo lokal supaya kedua sisi sinkron sebelum lanjut kerja.

---

## 🔴 Temuan hasil klik-langsung di server produksi (prioritas tertinggi)

> **Status: A, B, C, E sudah di-fix di repo lokal ini (belum di-deploy ke server).** F: bug sampingan (cache-buster numpuk) sudah di-fix, tapi gejala utama yang kamu laporkan belum berhasil direproduksi — lihat catatan di bagian F. D: **ralat**, ternyata bukan bug — lihat penjelasan di bagian D.

### A. ✅ FIXED — KRITIS: di lebar tablet (751–1024px), menu navigasi sama sekali tidak bisa dibuka
**Lokasi:** `css/mikhmon-custom.css` baris ~1362 vs `js/mikhmon-ui.dark.min.js` (handler `openNav`/`closeNav`/`resize`).

**Reproduksi (terverifikasi, diulang 3×):** buka `mikh.rivaldev.site` di lebar viewport 820px atau 900px (iPad portrait, tablet Android umum) → sidebar otomatis tertutup → tap ikon hamburger (☰) → **tidak terjadi apa-apa**. Menu Hotspot, Log, System, Report, Settings — semuanya jadi tidak terjangkau kecuali user tahu harus resize/rotate browser ke atas 1024px atau di bawah 751px.

**Akar masalah (dikonfirmasi lewat console):** ada dua sistem yang saling menimpa:
- CSS (baris 1362-1390, ditambahkan belakangan, komentar "Tablet: collapsible sidenav + 2-column KPI cards") memaksa `.sidenav{width:0 !important}` khusus di rentang 751–1024px — ini investasi bagus untuk KPI card 2-kolom di tablet.
- Tapi file JS `mikhmon-ui.dark.min.js` (lama, belum di-update) masih pakai logika 2-state saja: `window.innerWidth < 800` = mobile, `> 800` = desktop-pinned, dan saat tombol hamburang diklik ia coba men-set `sidenav.style = "width: 210px..."` **inline**. Inline style biasanya menang lawan CSS — tapi `!important` di CSS baru tetap menang lawan inline style apa pun. Hasilnya: klik hamburger di 751-1024px selalu kalah melawan `!important`, sidebar permanen tertutup, tidak ada error di console jadi user (dan developer yang cuma lihat console) tidak dapat petunjuk apa pun.

**Dampak:** setiap admin yang buka MIKFAST dari tablet (atau ponsel besar dalam mode landscape, atau browser desktop yang di-resize ke lebar itu) benar-benar terkunci dari seluruh menu selain halaman yang lagi dibuka. Ini bug fungsional, bukan cuma kosmetik.

**Fix yang diterapkan** (opsi 2 dari rekomendasi awal — class-based, bukan band-aid ganti angka doang):
- Kelima file `js/mikhmon-ui.{dark,light,blue,green,pink}.min.js`: threshold `800` → `1024` di ketiga tempat (klik `openNav`, klik `closeNav`, handler `resize`), dan sekarang toggle `document.body.classList.add/remove/toggle("mm-nav-open")` di semua handler itu — bukan cuma mengandalkan inline `style=`.
- `css/mikhmon-custom.css` blok tablet (~baris 1362): tambah `body.mm-nav-open .sidenav{width:210px !important; ...}` supaya ada rule `!important` yang menang saat nav dibuka (sebelumnya cuma ada rule `!important` untuk versi *tertutup*, tidak ada lawannya). Juga diperbaiki: sebelumnya `#openNav{display:block !important}` unconditional artinya tombol hamburger tidak pernah hilang & tombol close (`#closeNav`) tidak pernah muncul di tablet meski nav sudah kebuka — sekarang keduanya ikut merespons `body.mm-nav-open`, jadi ada cara untuk MENUTUP lagi menu di tablet (sebelumnya, walau bug utamanya kebetulan ke-fix, tetap tidak ada tombol close yang berfungsi).

Sudah diverifikasi ulang: dengan fix ini, JS-nya konsisten treat 751–1024px sama seperti mobile (overlay, bukan push) — sesuai maksud komentar CSS "Tablet: collapsible sidenav", dan >1024px tetap pinned permanen seperti desktop asli.

---

### B. ✅ FIXED — Dropdown sesi menampilkan sesi aktif dua kali
**Lokasi:** `include/menu.php` (blok `<select class="connect mm-sidenav-session">`), dikonfirmasi live: `document.querySelectorAll('.mm-sidenav-session option')` → `["kos", "kos ♦", "plampang"]`.

**Masalah:** ada `<option>` hardcoded untuk sesi aktif di awal, LALU loop-nya menambahkan lagi semua sesi termasuk yang aktif (ditandai ♦). Sesi yang sedang dipakai muncul dua kali di dropdown, sesi lain cuma sekali — user yang buka dropdown switcher bisa bingung "kos" vs "kos ♦" itu beda apa.

**Fix yang diterapkan:** loop `foreach` di `include/menu.php` sekarang skip saat `$sesname == $session` (opsi sudah ditulis manual sebelum loop, jadi tidak perlu ditulis ulang), dan opsi manual di awal ditandai `selected` + diberi tanda ♦ juga (konsisten dengan tanda yang tadinya cuma muncul di opsi kedua).

---

### C. ✅ FIXED — Dropdown "Quick Print QR" menampilkan "disable" dua kali
**Lokasi:** `settings/sessions.php` baris 131-135, dikonfirmasi live di halaman Admin Settings.

**Masalah:** `<option><?= $qrbt ?></option>` mencetak nilai tersimpan saat ini ("disable" secara default) sebagai opsi pertama, lalu diikuti `<option>enable</option><option>disable</option>` yang hardcoded — kalau setting saat ini "disable" (kasus paling umum, default), dropdown menampilkan "disable" dua kali.

**Fix yang diterapkan:** `settings/sessions.php` — dua `<option>` hardcoded sekarang pakai `selected` dinamis berdasarkan `$qrbt`, opsi ketiga yang mencetak `$qrbt` mentah-mentah dihapus.

---

### D. ❌ RALAT — bukan bug, saya salah baca
Awalnya saya kira ada dua "jam sekarang" yang beda ~5-6 jam (jam kecil di navbar vs label "Updated"). Setelah cek isi `js/mikhmon.bundle.legacy.js` (fungsi `idleTimer()`/`startTimer()`), elemen `#timer` itu **bukan jam** — itu **hitung mundur idle-logout** (format `menit:detik`, default mulai dari `10:00`, reset tiap ada gerakan mouse/klik/keyboard, auto-logout di `0:00`). Angka "9:57", "9:35", dst yang saya kira "jam" itu sebenarnya "9 menit 57 detik lagi sebelum auto-logout" — dan cocok persis polanya (selalu di bawah 10:00, turun seiring waktu saya diam nunggu response tool). Tidak ada yang perlu diperbaiki di sini. Maaf atas temuan palsunya.

---

### E. ✅ FIXED — Nav mobile/tablet: drawer tanpa scrim gelap (elemen overlay tidak pernah dipasang)
**Lokasi:** perilaku hamburger di lebar ≤750px; ada referensi `#overL` (overlay) di `mikhmon-ui.dark.min.js` tapi elemen itu **tidak pernah ada di DOM** (`document.getElementById('overL')` → `null`, dikonfirmasi live) — kode overlay-nya sudah direncanakan tapi tidak pernah selesai dipasang.

**Masalah:** saat drawer dibuka, sisa halaman di sebelah kanan drawer masih terlihat terang & masih bisa disentuh — tidak ada dimming yang menandakan "fokus sekarang di menu".

**Fix yang diterapkan:**
- `include/menu.php`: tambah `<div id="overL" onclick="...klik closeNav..."></div>` tepat sebelum `<div id="main">` (satu tempat, berlaku untuk kedua varian navbar/halaman admin & non-admin).
- `css/mikhmon-custom.css`: `#overL` diberi `position:fixed`, mulai dari bawah navbar (`top:51px`) sampai penuh, `background:rgba(0,0,0,.45)`, `z-index:8` (di bawah sidenav yang `z-index:9`), default `display:none`. Muncul lewat `body.mm-nav-open #overL{display:block}` dibungkus `@media (max-width:1024px)` — supaya TIDAK muncul di desktop asli (>1024px) yang sidenav-nya memang permanen terbuka, cuma muncul saat drawer mobile/tablet dibuka.
- Klik di area gelap ini akan memanggil `closeNav.click()` kalau elemennya ada, jadi berfungsi juga sebagai "tap di luar untuk menutup".

---

### F. Toggle dark/light: gejala utama belum berhasil direproduksi; bug sampingan sudah di-fix
Kamu laporkan toggle dark→light jalan, tapi light→dark berikutnya tidak jalan di HP. Sudah dicoba reproduksi dengan: klik biasa berkali-kali, klik cepat berturutan, klik dengan touch-emulation, klik setelah navigasi SPA (pindah halaman), dan reload penuh setelah ganti tema (persist tetap benar) — **semua berhasil normal di pengujian ini**, gejala yang kamu laporkan tidak berhasil saya reproduksi. Belum di-"fix" karena belum tahu apa yang sebenarnya rusak.

**✅ Bug sampingan yang ditemukan & sudah di-fix:** setiap toggle bikin request script grafik traffic baru (`hc.light.js`/`hc.dark.js`) dengan cache-buster `?t=<timestamp>` — tapi kodenya (`include/menu.php`, dua tempat, ~baris 290 & ~513) cuma cek `indexOf("?")===-1` untuk memutuskan pakai `?` atau `&`, sementara `src` yang dibaca untuk membangun URL berikutnya adalah `src` dari script sebelumnya yang **sudah** punya `?t=...`. Setiap toggle berikutnya numpuk jadi `?t=X&t=Y&t=Z...` tanpa batas. Fix: sekarang `src` di-strip query string-nya dulu (`.split("?")[0]`) sebelum dipakai, jadi cache-buster selalu bersih satu `?t=...` per request. Ini bukan penyebab langsung tombolnya "tidak jalan" (tidak ada indikasi itu memblokir apa pun), tapi tetap bug nyata yang sekarang beres.

**Untuk saya bisa reproduksi lebih tepat, tolong info:**
- Browser & device persis apa (mis. Chrome Android / Safari iOS, model HP)?
- Apakah tombol switch-nya sendiri terlihat bergerak/berubah posisi saat ditekan, atau benar-benar tidak merespon sentuhan sama sekali?
- Apakah kejadiannya selalu di percobaan toggle kedua persis setelah yang pertama, atau baru gagal setelah sempat pindah-pindah halaman dulu?
- Koneksi saat itu WiFi kencang atau data seluler yang lambat?

Kalau bisa dapat salah satu dari itu, saya bisa coba reproduksi ulang dengan kondisi yang lebih mirip.

---

### G. Konfirmasi temuan Tahap 1 dengan data asli (170 user nyata)
Tabel Users (10 kolom, `text-nowrap`) dan ikon aksi kecil (hapus/lock/print/sort) terkonfirmasi persis seperti prediksi code-review: di lebar 820px maupun 390px, tabel tetap butuh scroll horizontal tanpa indikator visual, dan ikon aksi (≈16-20px, jarak antar-ikon sempit) berisiko salah-tap di layar sentuh. Lihat temuan #4 di bagian Tahap 1 di bawah untuk detail & saran.

---

## Ringkasan (Tahap 1 — code review repo lokal)

Sistem grid & tema (`css/mikhmon-ui.*.css` + `mikhmon-custom.css`) sudah cukup rapi untuk ukuran proyek legacy PHP — ada dark/light theme, skeleton loader, toast. Tapi ada **satu breakpoint tunggal (750px)** yang membuat rentang tablet (751–1024px) tidak pernah benar-benar dioptimalkan, ditambah beberapa **inkonsistensi lintas file** (kelas tombol, dialog konfirmasi, pola form) yang muncul karena halaman-halaman dibangun terpisah dari waktu ke waktu tanpa komponen bersama.

Prioritas tertinggi: **#1 navbar overflow:hidden** (bisa membuat kontrol logout/tema/bahasa tidak bisa diklik di HP kecil) dan **#3 offset overlay loading yang salah** (bug visual nyata, bukan cuma preferensi).

---

## Temuan

### 1. Navbar kanan bisa terpotong tanpa peringatan di layar sempit
**Lokasi:** `css/mikhmon-ui.light.min.css` → `.navbar{overflow:hidden; height:50px}` ; markup di `include/dashboard-header.php` & `include/menu.php` (navbar-right berisi: link Logout bertuliskan teks, chip idle-timer `width:70px`, language dropdown, theme toggle).

**Masalah:** Tidak ada media query yang mengecilkan/menyembunyikan elemen `.navbar-right` di layar sempit. Karena container navbar memakai `overflow:hidden`, begitu total lebar (Logout + idle timer + language dropdown + theme toggle) melebihi sisa ruang di layar ±320–375px, elemen paling kanan **hilang begitu saja** dari viewport — bukan wrap, bukan scroll, benar-benar terpotong dan tidak bisa diakses lewat scroll horizontal apa pun.

**Dampak:** Di HP kecil (iPhone SE, Android low-end 360px), user berpotensi tidak bisa logout atau ganti tema/bahasa karena tombolnya terpotong `overflow:hidden`.

**Saran implementasi:**
- Breakpoint ≤480px: sembunyikan teks "Logout" (`.navbar-right #logout .label`), sisakan ikon saja.
- Pindahkan idle-timer chip, language dropdown, dan theme toggle ke dalam sidenav/hamburger menu di mobile, atau jadikan overflow menu (ikon "⋮" yang membuka dropdown berisi ketiganya).
- Ganti `overflow:hidden` pada `.navbar` khusus mobile menjadi flex-wrap terkontrol dengan `min-height` bukan `height` tetap, supaya kalau meleset tidak memotong konten secara diam-diam.

---

### 2. Tidak ada layout tablet — cuma "mobile" vs "desktop"
**Lokasi:** `css/mikhmon-ui.light.min.css` — satu-satunya breakpoint struktural: `@media screen and (max-width:750px)`. Di atas 750px, sidenav langsung fixed 210px dan grid `col-4`/`col-8` desktop penuh (lihat `dashboard/home.php` — 3× `col-4` untuk KPI card, `col-8`+`col-4` untuk traffic chart & log).

**Masalah:** Tablet portrait umum (iPad Mini/Air 768–834px, Android tablet 800px) jatuh ke sisi "desktop" dari breakpoint ini. Sidenav 210px tetap terbuka permanen (tidak collapsible di rentang ini karena `#openNav{display:none}` hanya aktif ≥750px), menyisakan ±550–620px untuk konten. 3 kartu KPI @ `col-4` (33%) masing-masing hanya dapat ±180–200px net — padahal `.mm-kpi__value{font-size:44px}` didesain untuk kartu yang lebih lebar. Hasilnya angka besar + ikon 28px berdesakan di kartu sempit.

**Dampak:** Pengalaman "tanggung" khas tablet: bukan mobile (dapat versi ringkas), bukan desktop (dapat ruang cukup) — dapat layout desktop dipaksa masuk ke ruang lebih kecil.

**Saran implementasi:**
- Tambah breakpoint menengah, mis. `@media (max-width: 1024px)`: sidenav jadi collapsible (bukan permanen terbuka) seperti mode mobile, tapi tetap pakai grid multi-kolom untuk kartu.
- Atau: turunkan KPI row dari 3 kolom jadi 2 kolom di rentang 751–1024px (`col-4` → `col-6`) supaya tiap kartu tetap lega tanpa harus full-stack seperti mobile.

---

### 3. Overlay loading & skeleton salah offset di desktop — bug visual nyata
**Lokasi:** `css/mikhmon-custom.css` baris ~978–991 (`#loading{ left: calc(260px + 50%) }`) dan baris ~1082–1107 (`#mmPageSkeleton{ left: 260px }`), dibanding lebar sidenav aktual di `css/mikhmon-ui.light.min.css` (`.sidenav{width:210px}` pada desktop).

**Masalah:** Kedua overlay ini dihitung berdasarkan asumsi sidenav selebar **260px**, padahal implementasi aktual sidenav adalah **210px**. Selisih 50px ini membuat:
- Spinner loading (`#loading`) tidak center terhadap area konten — bergeser ke kanan.
- Skeleton overlay (`#mmPageSkeleton{left:260px; right:0}`) menyisakan celah 50px di sisi kiri area konten (antara tepi sidenav dan mulainya overlay) di mana konten halaman lama masih terlihat sekilas saat transisi — mengganggu efek "skeleton loading" yang seharusnya menutup penuh area konten.

**Dampak:** Ini murni bug, bukan soal selera — nilainya harus konsisten dengan `.sidenav{width:210px}` yang sudah didefinisikan sebagai source of truth.

**Saran implementasi:** Ganti kedua nilai hardcoded `260px` menjadi `210px` (atau, lebih tahan-lama, definisikan custom property `--mm-sidenav-w: 210px` sekali di root dan pakai di ketiga tempat — sidenav, `#main`, `#loading`, `#mmPageSkeleton` — supaya tidak drift lagi kalau lebar sidenav berubah nanti).

---

### 4. Tabel data: scroll horizontal tanpa affordance + target sentuh terlalu kecil
**Lokasi:** `hotspot/users.php` baris 181–195 — tabel 10 kolom (`Print, Server, Name, Print, Profile, MAC Address, Uptime, Bytes In, Bytes Out, Comment`) dengan class `table table-bordered table-hover text-nowrap`, dibungkus `<div class="overflow ...">` yang di CSS berarti `overflow-x:auto`. Pola sama berulang di halaman list lain (vouchers, active sessions, dsb).

**Masalah:**
- Tidak ada indikator visual bahwa tabel bisa di-scroll ke samping (tidak ada shadow gradient di tepi, tidak ada label "geser untuk lihat lebih"). Di mobile, user harus tidak sengaja menyentuh dan menggeser untuk sadar ada kolom tersembunyi.
- Aksi hapus/print per baris adalah `<i class="fa fa-minus-square">` polos dengan `onclick` langsung, tanpa padding/`<button>` wrapper — area sentuh nyata jauh di bawah 44×44px yang direkomendasikan untuk target sentuh mobile (WCAG 2.5.5 / Apple & Material HIG). Sulit ditekan akurat di layar HP, apalagi berdekatan dengan ikon sort di header kolom lain.

**Saran implementasi:**
- Tambah CSS shadow "fade" di kiri/kanan container `.overflow` yang muncul kondisional saat konten ter-scroll (bisa pure-CSS pakai `background-attachment: local` scroll-shadow trick, tanpa JS tambahan).
- Bungkus ikon aksi (`fa-minus-square`, ikon sort, ikon print) dengan padding minimal 10–12px atau `min-width/min-height:40px` di breakpoint mobile, supaya area sentuh mendekati 44px tanpa mengubah ukuran ikon visual.
- Untuk mobile khususnya, pertimbangkan mode "card list" alternatif untuk tabel user (1 baris = 1 card ringkas: nama, profil, uptime, tombol aksi) di ≤750px — pola umum untuk data table di admin panel mobile-first.

---

### 5. Form "label | input" tidak restack di HP — hanya panel luar yang stack
**Lokasi:** `hotspot/adduser.php` baris 99–181 dan pola sejenis di `generateuser.php`, `userprofile.php`. Struktur: `<div class="col-8">…<table class="table"><td>Label</td><td><input></td></table>`. CSS terkait: `css/mikhmon-custom.css` baris 1274–1301 (`.mm-user-form-row td:first-child{width:34%}`, `td:last-child{width:66%}`), dan breakpoint `@media (max-width:1100px)` yang hanya men-stack **panel** `col-8`/`col-4`, bukan struktur **tabel di dalamnya**.

**Masalah:** Split 34%/66% pada `<td>` label/input bersifat fixed-percentage di semua lebar layar. Di HP (~360–390px lebar efektif), label seperti "Data Limit" atau "Time Limit" harus muat di ±120px sambil input di sebelahnya makin sempit — berbeda dari pola form mobile yang umum (label di atas, input di bawah, full width).

**Saran implementasi:** Tambahkan breakpoint kecil, mis. `@media (max-width:480px)`, yang mengubah `.mm-user-form-row table, tr, td{display:block; width:100% !important}` — label jadi baris sendiri di atas input-nya. Ini perubahan CSS murni, tidak menyentuh PHP.

---

### 6. Aksi destruktif pakai `confirm()` browser native, bukan komponen tema sendiri
**Lokasi:** `hotspot/users.php` baris 155, 157, 228 dan sejenis di `hotspot/hosts.php`, `hotspot/ipbinding.php`, `hotspot/cookies.php`, dll — semua pola `onclick="if(confirm('Are you sure...')){...}"`.

**Masalah:** Aplikasi sudah berinvestasi di sistem toast & skeleton bertema (`mikhmon-toast.php`, `ui-toast.js`) yang mengikuti dark/light theme — tapi untuk konfirmasi hapus (aksi paling berisiko: hapus user, hapus host, hapus cookie), yang muncul adalah dialog `confirm()` bawaan browser: putih polos, tidak mengikuti tema, styling beda-beda per OS/browser, dan di sebagian browser mobile bisa terasa "melompat"/delay.

**Saran implementasi:** Ganti `confirm()` dengan modal konfirmasi kecil bertema (bisa reuse pola `.modal-window` yang sudah ada di CSS, `[class*=col-]` sudah responsive ≤750px). Tidak perlu library baru — cukup 1 komponen modal generik dipakai ulang di semua tombol hapus.

---

### 7. Dua nama class untuk satu warna yang sama (`bg-red` vs `bg-danger`)
**Lokasi:** `bg-red` dipakai di `hotspot/users.php`, `hotspot/userbyprofile.php`, `hotspot/listquickprint.php`, `hotspot/quickprint.php`, `settings/sessions.php`, `dashboard/aload.php`; `bg-danger` dipakai di `hotspot/userbyname.php`, `hotspot/generateuser.php`, `settings/uplogo.php`, `settings/vouchereditor.php`, `settings/settings.php`. Keduanya resolve ke warna identik (`#f86c6b`) di kedua tema (`css/mikhmon-ui.light.min.css`, `mikhmon-ui.dark.min.css`).

**Masalah:** Saat ini tidak menghasilkan bug visual (kebetulan warnanya sama), tapi ini tanda tidak ada konvensi/komponen bersama yang diikuti developer di file berbeda. Risikonya: siapa pun yang restyle salah satu nanti (misal desainer minta warna bahaya sedikit lebih gelap untuk kontras) besar kemungkinan cuma edit satu class, dan separuh tombol hapus di aplikasi tetap warna lama — inkonsistensi baru yang tidak disengaja.

**Saran implementasi:** Pilih satu nama (disarankan `bg-danger` karena lebih semantik dari `bg-red`), lalu cari-ganti semua pemakaian `bg-red` di 6 file di atas. Tidak berisiko karena secara visual hasilnya identik.

---

### 8. Login: placeholder sebagai pengganti label, form pakai `<table>`, autocomplete dimatikan
**Lokasi:** `include/login.php` baris 36–58.

**Masalah:**
- Input username/password tidak punya `<label>`, hanya `placeholder="Username"` / `"Password"` — begitu user mulai mengetik, petunjuk field hilang (masalah usability klasik, lebih terasa di mobile dengan keyboard menutupi separuh layar).
- Layout form pakai `<table>` (`<table class="table"><tr><td>...`) — pola lama, secara semantik bukan form yang sesungguhnya, menyulitkan screen reader mengasosiasikan field.
- `<form autocomplete="off">` secara eksplisit mematikan autofill browser/password manager. Ini friksi ekstra terutama di mobile, di mana user sangat mengandalkan autofill karena mengetik password di keyboard virtual lebih lambat.

**Saran implementasi:**
- Tambah `<label class="sr-only">` (visually-hidden tapi terbaca screen reader) di atas tiap input, atau floating label sederhana.
- Ganti `<table>` jadi `<div>`/`<fieldset>` biasa — tidak menambah baris kode signifikan, cuma ganti tag.
- Hapus `autocomplete="off"` dari `<form>`; kalau tujuannya mencegah browser mengisi field lain (bukan username/password), cukup set `autocomplete="username"` dan `autocomplete="current-password"` di masing-masing input.

---

### 9. Aksesibilitas nama ikon tidak konsisten — sebagian dapat `aria-label`, sebagian tidak
**Lokasi:** Bandingkan `include/dashboard-header.php` (theme toggle & tombol refresh sudah pakai `aria-label`/`title` rapi) dengan ikon aksi di `hotspot/users.php` (`fa-minus-square`, ikon sort `fa-sort`, ikon print) yang hanya punya `title` tanpa `aria-label`, dan beberapa tanpa atribut aksesibilitas sama sekali.

**Masalah:** `title` tidak dibacakan andal oleh semua screen reader saat elemen bukan link/button semantik (di sini `<i>` polos dengan `onclick`), dan `title` **tidak pernah muncul di perangkat sentuh** (tidak ada hover) — jadi di tablet/HP, ikon-ikon ini benar-benar tanpa penjelasan apa pun bagi pengguna assistive tech.

**Saran implementasi:** Standardisasi: setiap ikon interaktif dibungkus `<button type="button" aria-label="...">` (bukan `<i onclick>` telanjang), konsisten dengan pola yang sudah dipakai di theme toggle. Bisa dilakukan bertahap dimulai dari tabel users & vouchers karena paling sering dipakai.

---

## Ringkasan Prioritas Implementasi

| # | Temuan | Kompleksitas fix | Dampak | Prioritas |
|---|--------|----------|--------|-----------|
| 3 | Offset overlay loading/skeleton salah (260px vs 210px) | Sangat kecil (ganti 2 angka) | Bug visual nyata di semua halaman desktop | **Tinggi** |
| 1 | Navbar kanan bisa terpotong di HP sempit | Kecil–sedang (CSS + sedikit markup) | Fitur logout/tema/bahasa bisa tak terjangkau | **Tinggi** |
| 4 | Target sentuh ikon aksi tabel terlalu kecil | Kecil (CSS padding) | Salah tap hapus user/host di mobile | **Tinggi** |
| 6 | `confirm()` native untuk aksi hapus | Sedang (1 komponen modal reusable) | Konsistensi visual + rasa "aman" saat aksi destruktif | Sedang |
| 5 | Form label/input tidak restack di HP | Kecil (1 media query) | Kenyamanan mengisi form di HP | Sedang |
| 2 | Tidak ada layout tablet dedicated | Sedang | Pengalaman "tanggung" di tablet | Sedang |
| 9 | aria-label tidak konsisten di ikon aksi | Sedang (banyak titik sentuh, tapi tiap edit ringan) | Aksesibilitas mobile/screen reader | Sedang |
| 8 | Login: placeholder-only label, table layout, autocomplete off | Kecil | Usability login di mobile, sedikit aksesibilitas | Rendah–Sedang |
| 7 | `bg-red` vs `bg-danger` duplikat | Sangat kecil (cari-ganti) | Cegah drift visual di masa depan | Rendah |

**Rekomendasi urutan kerja:** mulai dari #3 (ubah 2 nilai CSS, tanpa risiko), lalu #1 dan #4 (murni CSS, dampak mobile langsung terasa), baru masuk ke #6 dan #2 yang butuh sedikit lebih banyak kerja tapi bukan perubahan arsitektur besar.
