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
