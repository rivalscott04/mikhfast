<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
}
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> About</h3>
      </div>
      <div class="card-body">
        <h3>MIKFAST V<?= $_SESSION['v']; ?></h3>

        <div class="about-changelog">
          <h4>Changelog</h4>
          <ul>
            <li>
              <strong>RouterOS v6 &amp; v7</strong> — adapter layer (<code>RouterService</code>, <code>Ros6Adapter</code>, <code>Ros7Adapter</code>) supaya modul/UI tidak perlu menangani perbedaan versi API.
            </li>
            <li>
              <strong>Request API lebih ringan</strong> — pembatasan field (proplist) dan normalisasi output agar payload kecil, parsing cepat, dan lebih stabil.
            </li>
            <li>
              <strong>Branding MIKFAST V3</strong> — logo/ikon SVG, sidebar 2-level lebih simple, dan komponen UI lebih konsisten untuk workflow voucher.
            </li>
            <li>
              <strong>Tema terang &amp; gelap</strong> — toggle tema dengan styling light theme yang diperbarui (progress bar, custom select, kontras lebih baik).
            </li>
            <li>
              <strong>Dropdown bahasa</strong> — ganti bahasa dari navbar; dukungan string ID/EN dan handler session yang lebih rapi.
            </li>
            <li>
              <strong>Navigasi SPA/AJAX</strong> — halaman aman dimuat tanpa full reload; ada page skeleton + backdrop saat transisi, dengan fallback ke navigasi biasa jika gagal.
            </li>
            <li>
              <strong>Toast notifikasi</strong> — feedback sukses/gagal menggantikan alert kaku; termasuk notifikasi sekali saat ganti session router.
            </li>
            <li>
              <strong>Dashboard lebih responsif</strong> — cache singkat untuk system resource, hitungan hotspot, dan log supaya tidak bolak-balik request ke RouterOS setiap refresh.
            </li>
            <li>
              <strong>System Information</strong> — CPU / RAM / HDD dengan indikator bar + persentase used dan tooltip.
            </li>
            <li>
              <strong>Hotspot log &amp; app log</strong> — log disiapkan lebih dulu, tetap tampil walau reload/AJAX belum jalan; app log ditambahkan di dashboard.
            </li>
            <li>
              <strong>Traffic chart &amp; KPI</strong> — inisialisasi chart diperbaiki, tampilan income/KPI dashboard diperbarui.
            </li>
            <li>
              <strong>Template Editor voucher</strong> — perubahan editor dipastikan ikut tersimpan saat Save; shortcut simpan cepat <kbd>Ctrl+S</kbd> / <kbd>Cmd+S</kbd>; cek permission file template.
            </li>
            <li>
              <strong>Upload logo otomatis</strong> — file logo otomatis disimpan sebagai <code>logo-{session}.png</code>; toast feedback saat upload/hapus; validasi permission folder <code>img/</code>.
            </li>
            <li>
              <strong>Form AJAX</strong> — submit form (settings, upload, dll.) dengan parsing respons JSON dan error handling yang lebih jelas.
            </li>
            <li>
              <strong>Laporan selling</strong> — inisialisasi dan penomoran baris diperbaiki agar lebih mudah dibaca.
            </li>
            <li>
              <strong>Optimasi lainnya</strong> — hapus dependency Pace yang tidak dipakai; perbaikan session handling, timer, dan sidebar accordion.
            </li>
          </ul>
        </div>

        <hr class="about-divider">

        <div class="about-credits">
          <h4>Credit</h4>
          <p>
            Aplikasi ini dipersembahkan untuk pengusaha hotspot di manapun Anda berada.
            Semoga makin sukses.
          </p>
          <ul>
            <li>
              <strong>Author asli</strong> : Laksamadi Guko
            </li>
            <li>
              <strong>Modified by</strong> : Rival
            </li>
            <li>
              <strong>Licence</strong> : <a href="https://github.com/laksa19/mikhmonv2/blob/master/LICENSE">GPLv2</a>
            </li>
            <li>
              <strong>API Class</strong> : <a href="https://github.com/BenMenking/routeros-api">routeros-api</a>
            </li>
            <li>
              <strong>Website</strong> : <a href="https://laksa19.github.io">laksa19.github.io</a>
            </li>
            <li>
              <strong>Facebook</strong> : <a href="https://fb.com/laksamadi">fb.com/laksamadi</a>
            </li>
          </ul>
          <p>
            Terima kasih untuk semua yang telah mendukung pengembangan MIKFAST.
          </p>
        </div>

        <div class="about-copy">
          <i>Copyright &copy; 2018 Laksamadi Guko</i>
        </div>
      </div>
    </div>
  </div>
</div>
