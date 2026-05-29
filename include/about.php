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
            <li>RouterOS v6 &amp; v7. Mendukung adapter layer (<code>RouterService</code>, <code>Ros6Adapter</code>, <code>Ros7Adapter</code>) agar modul/UI tidak perlu menangani perbedaan versi API.</li>
            <li>Request API lebih ringan. Pembatasan field (proplist) dan normalisasi output agar payload kecil, parsing cepat, dan lebih stabil.</li>
            <li>Branding MIKFAST V3. Logo/ikon SVG, sidebar 2-level lebih simple, dan komponen UI lebih konsisten untuk workflow voucher.</li>
            <li>Tema terang &amp; gelap. Toggle tema dengan styling light theme yang diperbarui (progress bar, custom select, kontras lebih baik).</li>
            <li>Dropdown bahasa. Ganti bahasa dari navbar, dukungan string ID/EN, dan handler session yang lebih rapi.</li>
            <li>Navigasi SPA/AJAX. Halaman aman dimuat tanpa full reload, ada page skeleton + backdrop saat transisi, dengan fallback ke navigasi biasa jika gagal.</li>
            <li>Toast notifikasi. Feedback sukses/gagal menggantikan alert kaku, termasuk notifikasi sekali saat ganti session router.</li>
            <li>Dashboard lebih responsif. Cache singkat untuk system resource, hitungan hotspot, dan log supaya tidak bolak-balik request ke RouterOS setiap refresh.</li>
            <li>System Information. CPU / RAM / HDD dengan indikator bar, persentase used, dan tooltip.</li>
            <li>Hotspot log &amp; app log. Log disiapkan lebih dulu dan tetap tampil walau reload/AJAX belum jalan. App log ditambahkan di dashboard.</li>
            <li>Traffic chart &amp; KPI. Inisialisasi chart diperbaiki dan tampilan income/KPI dashboard diperbarui.</li>
            <li>Template Editor voucher. Perubahan editor dipastikan ikut tersimpan saat Save, shortcut simpan cepat <kbd>Ctrl+S</kbd> / <kbd>Cmd+S</kbd>, serta cek permission file template.</li>
            <li>Upload logo otomatis. File logo otomatis disimpan sebagai <code>logo-{session}.png</code> dengan toast feedback saat upload/hapus dan validasi permission folder <code>img/</code>.</li>
            <li>Form AJAX. Submit form (settings, upload, dll.) dengan parsing respons JSON dan error handling yang lebih jelas.</li>
            <li>Laporan selling. Inisialisasi dan penomoran baris diperbaiki agar lebih mudah dibaca.</li>
            <li>Optimasi lainnya. Hapus dependency Pace yang tidak dipakai, perbaikan session handling, timer, dan sidebar accordion.</li>
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
            <li>Author asli : Laksamadi Guko</li>
            <li>Modified by : Rival</li>
            <li>Licence : <a href="https://github.com/laksa19/mikhmonv2/blob/master/LICENSE">GPLv2</a></li>
            <li>API Class : <a href="https://github.com/BenMenking/routeros-api">routeros-api</a></li>
            <li>Website : <a href="https://laksa19.github.io">laksa19.github.io</a></li>
            <li>Facebook : <a href="https://fb.com/laksamadi">fb.com/laksamadi</a></li>
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
