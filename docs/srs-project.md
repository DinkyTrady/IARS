> [!WARNING]
> This file based on [laporan-pdf.md](./laporand-pdf.md)

Berikut adalah versi lengkap dan terperinci dari _Software Requirements Specification_ (SRS) berdasarkan dokumen penelitian yang Anda unggah. Dokumen ini disusun dengan struktur standar untuk pengembangan perangkat lunak.

# SOFTWARE REQUIREMENTS SPECIFICATION (SRS)

**Sistem Informasi Reservasi Ruangan Kampus Berbasis Web**
**Disusun berdasarkan penelitian Kelompok 7, UNESA (2026)**

## 1. PENDAHULUAN

### 1.1 Tujuan Dokumen

Dokumen ini mendefinisikan spesifikasi perangkat lunak untuk "Sistem Informasi Reservasi Ruangan Kampus Berbasis Web". Sistem ini dikembangkan untuk mengoptimalkan jadwal perkuliahan dan kegiatan mahasiswa agar tidak terjadi benturan jadwal.

### 1.2 Ruang Lingkup Sistem

Sistem ini dirancang untuk menggantikan proses pengelolaan reservasi ruangan yang masih dilakukan secara manual atau semi-digital melalui pencatatan administrasi atau _spreadsheet_ terpisah. Sistem ini mengintegrasikan manajemen reservasi, validasi konflik jadwal, serta optimalisasi penggunaan ruangan.

### 1.3 Manfaat Sistem

- **Bagi Mahasiswa dan Dosen:** Mempermudah pemesanan ruangan, memberikan informasi jadwal secara _real-time_, dan mengurangi potensi benturan kegiatan.
- **Bagi Pengelola Kampus:** Membantu pengelolaan fasilitas ruangan agar lebih sistematis, terintegrasi, transparan, dan optimal.

## 2. DESKRIPSI KESELURUHAN (_OVERALL DESCRIPTION_)

### 2.1 Perspektif Produk

Sistem ini adalah aplikasi berbasis web yang dapat diakses melalui jaringan internet atau intranet kampus. Basis data digunakan untuk menyimpan informasi terkait pengguna, ruangan, jadwal perkuliahan, serta kegiatan mahasiswa secara konsisten dan _real-time_.

### 2.2 Karakteristik Pengguna (Aktor)

Terdapat dua aktor utama yang berinteraksi dengan sistem ini:

1.  **Pengguna (Mahasiswa / Dosen):** Aktor yang dapat melihat jadwal, ketersediaan ruangan, dan mengajukan permintaan pemesanan ruangan.
2.  **Admin:** Aktor dari pihak pengelola kampus yang memiliki hak akses penuh untuk mengelola data master (ruangan, pengguna, jadwal) dan berwenang menyetujui atau menolak permintaan reservasi.

## 3. KEBUTUHAN FUNGSIONAL (_FUNCTIONAL REQUIREMENTS_)

Kebutuhan fungsional mendefinisikan fungsi-fungsi spesifik yang wajib dimiliki oleh sistem.

- **FR-01: Manajemen Ketersediaan Ruangan**
    - Sistem dapat menampilkan daftar ruangan yang tersedia.
- **FR-02: Pemesanan Ruangan**
    - Pengguna (Mahasiswa/Dosen) dapat melakukan pemesanan ruangan melalui sistem.
- **FR-03: Visibilitas Jadwal**
    - Sistem dapat menampilkan jadwal penggunaan ruangan.
- **FR-04: Validasi Konflik Jadwal**
    - Sistem dapat melakukan validasi untuk mencegah terjadinya konflik jadwal secara otomatis.
- **FR-05: Manajemen Data (CRUD)**
    - Admin dapat mengelola data ruangan dan data reservasi.
- **FR-06: Manajemen Persetujuan (_Approval_)**
    - Admin dapat menyetujui atau menolak permintaan reservasi ruangan yang diajukan oleh pengguna.

## 4. KEBUTUHAN NON-FUNGSIONAL (_NON-FUNCTIONAL REQUIREMENTS_)

Kebutuhan non-fungsional berkaitan dengan standar kualitas dan batasan teknis sistem.

- **NFR-01: Aksesibilitas Web**
    - Sistem harus dapat diakses melalui _browser web_.
- **NFR-02: _Usability_ (Kemudahan Penggunaan)**
    - Sistem harus memiliki antarmuka yang mudah digunakan oleh seluruh lapisan pengguna.
- **NFR-03: Integritas Basis Data**
    - Sistem mampu menyimpan data secara terstruktur dalam basis data. Data yang disimpan meliputi data pengguna, ruangan, reservasi, dan jadwal.
- **NFR-04: Konkurensi**
    - Sistem harus dapat diakses oleh beberapa pengguna secara bersamaan (_multi-user_).

## 5. LOGIKA SISTEM DAN OPTIMASI PENJADWALAN

Untuk menjalankan fungsi optimasi jadwal, sistem ini mengimplementasikan algoritma khusus.

### 5.1 Implementasi _Genetic Algorithm_ (GA)

Sistem menggunakan _Genetic Algorithm_ untuk mencari kombinasi jadwal terbaik yang meminimalkan konflik ruangan, dosen, dan waktu. GA adalah metode optimasi yang meniru proses seleksi alam.
Proses kerjanya meliputi tahapan berikut:

1.  Inisialisasi populasi awal jadwal.
2.  Evaluasi nilai _fitness_ pada tiap solusi penjadwalan.
3.  Seleksi solusi terbaik.
4.  Proses _crossover_ dan mutasi.
5.  Iterasi diulang hingga diperoleh solusi optimal untuk penjadwalan.

## 6. METODOLOGI PENGEMBANGAN DAN PENGUJIAN

### 6.1 Metode Pengembangan

Sistem dikembangkan menggunakan metode **Waterfall**. Tahapannya meliputi:

1.  **Analisis Kebutuhan:** Mengidentifikasi kebutuhan sistem.
2.  **Perancangan Sistem:** Merancang arsitektur, alur (_Use Case & Activity Diagram_), dan desain basis data.
3.  **Implementasi Sistem:** Pengembangan menggunakan bahasa pemrograman web dan basis data.
4.  **Pengujian Sistem:** Memastikan sistem berjalan sesuai kebutuhan.

### 6.2 Metode Pengujian

Sistem akan diuji menggunakan metode **Black Box Testing**. Pengujian ini berfokus pada pengujian setiap fungsi sistem dengan memberikan berbagai input dan mengamati apakah output yang dihasilkan sudah sesuai dengan yang diharapkan, tanpa perlu melihat kode program di dalamnya.
