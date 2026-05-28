### MIKHMON V3 (Fork/Development)

Mikhmon v3 adalah aplikasi web untuk membantu admin hotspot MikroTik (voucher, user, report, monitoring) lewat RouterOS API.

Repo ini adalah pengembangan dari **MIKHMON V3** dengan fokus ke **kompatibilitas RouterOS v6 & v7**, serta beberapa **optimasi komunikasi API** supaya lebih rapi dan lebih ringan.

#### Yang kita kerjakan di repo ini
- **Pondasi kompatibilitas ROS6/ROS7 (Adapter)**: komunikasi ke RouterOS dipusatkan lewat `lib/router/RouterService.php` yang memilih `Ros6Adapter` atau `Ros7Adapter` sehingga UI/module tidak perlu “tau” detail perbedaan versi.
- **“Deteksi/pemilihan” RouterOS major version (v6/v7)**: saat ini pemilihan adapter dilakukan via parameter `rosMajorVersion` saat membuat `RouterService`. Ini jadi landasan untuk auto-detect versi/capability di tahap berikutnya.
- **Optimasi query RouterOS API (lebih ringan)**: beberapa request sudah memakai daftar field (proplist) agar hanya mengambil kolom yang dibutuhkan (payload lebih kecil, parsing lebih cepat, lebih stabil).
- **Normalisasi output**: beberapa hasil query dikembalikan dalam format yang konsisten (mis. `getIdentity()` selalu mengembalikan `["name" => "..."]`).

#### Catatan singkat
- **RouterOS v6/v7**: itu versi firmware MikroTik. Kadang endpoint/field API bisa beda tipis, jadi aplikasi lama bisa “ngadat” kalau dipakai di versi baru.
- **Adapter**: “penerjemah” yang bikin kode aplikasi tetap rapi — yang beda-beda ditangani di satu tempat.

### Changelog (fork ini)
#### Unreleased
- Tambah `RouterService` + `RouterAdapterInterface` untuk memusatkan komunikasi RouterOS.
- Tambah `Ros6Adapter` dan `Ros7Adapter` sebagai pondasi kompatibilitas RouterOS v6/v7.
- Optimasi beberapa request RouterOS dengan pembatasan field (proplist) supaya payload lebih kecil.

#### Credit / Penghargaan
- **Author asli Mikhmon v3**: **Laksa19** 
  - Website: `https://laksa19.github.io/`
  - Repo upstream (referensi): `https://github.com/laksa19/mikhmonv3`

---
