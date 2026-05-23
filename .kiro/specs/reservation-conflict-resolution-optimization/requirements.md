# Dokumen Kebutuhan

## Pendahuluan

Dokumen ini menjelaskan kebutuhan untuk mengoptimalkan sistem resolusi konflik reservasi pada aplikasi reservasi ruangan kampus. Sistem saat ini menggunakan Algoritma Genetika (GA) untuk menyelesaikan konflik antara reservasi ruangan dan jadwal akademik. Optimasi ini berfokus pada peningkatan alur validasi, peningkatan algoritma GA parsial, peningkatan pengalaman pengguna, dan penambahan kemampuan logging dan monitoring yang komprehensif.

Optimasi ini menangani empat area utama:
1. Melonggarkan validasi frontend untuk mengizinkan pengajuan dengan konflik
2. Mengoptimalkan algoritma GA parsial untuk performa dan akurasi yang lebih baik
3. Meningkatkan pengalaman admin dan user dengan visibilitas konflik yang lebih baik
4. Menambahkan logging dan monitoring untuk debugging dan audit trail

## Glosarium

- **Sistem**: Aplikasi reservasi ruangan kampus
- **Reservasi**: Permintaan oleh pengguna untuk memesan ruangan pada tanggal dan waktu tertentu
- **Jadwal_Akademik**: Sesi kelas terjadwal yang menempati ruangan pada hari dan waktu tertentu
- **Konflik**: Situasi di mana dua atau lebih aktivitas memerlukan ruangan yang sama pada waktu yang tumpang tindih
- **GA_Parsial**: Algoritma Genetika yang hanya merelokasi jadwal akademik yang berkonflik tanpa memodifikasi jadwal lainnya
- **Kromosom**: Satu set lengkap jadwal akademik yang merepresentasikan satu solusi yang mungkin
- **Gen**: Satu entri jadwal akademik dalam kromosom
- **Skor_Fitness**: Nilai numerik (0 hingga 1) yang menunjukkan seberapa baik kromosom memenuhi semua constraint
- **Penalti**: Nilai numerik yang ditambahkan ketika constraint dilanggar, mengurangi skor fitness
- **Generasi**: Satu iterasi dari proses evolusi algoritma genetika
- **Pengguna**: Mahasiswa atau dosen yang membuat reservasi
- **Admin**: Administrator sistem yang menyetujui atau menolak reservasi
- **Catatan_Konflik**: Pesan yang dihasilkan secara otomatis yang disimpan di field note reservasi yang menunjukkan konflik yang terdeteksi

## Kebutuhan

### Kebutuhan 1: Validasi yang Dilonggarkan untuk Pengajuan Reservasi

**User Story:** Sebagai pengguna, saya ingin mengajukan reservasi meskipun konflik terdeteksi, sehingga saya dapat meminta pertimbangan khusus dari administrator untuk kebutuhan ruangan yang mendesak.

#### Kriteria Penerimaan

1. WHEN pengguna mengajukan reservasi melalui form create-reservation, THE Sistem SHALL menerima pengajuan terlepas dari konflik yang terdeteksi
2. WHEN pengguna mengajukan reservasi melalui modal room-list, THE Sistem SHALL menerima pengajuan terlepas dari konflik yang terdeteksi
3. WHEN konflik dengan Jadwal_Akademik terdeteksi saat pengajuan, THE Sistem SHALL menyimpan Catatan_Konflik di field note reservasi
4. WHEN konflik dengan Reservasi lain terdeteksi saat pengajuan, THE Sistem SHALL menyimpan Catatan_Konflik di field note reservasi
5. WHEN reservasi dengan konflik diajukan, THE Sistem SHALL mengatur status menjadi "pending"
6. WHEN reservasi dengan konflik diajukan, THE Sistem SHALL menampilkan pesan toast peringatan kepada pengguna
7. WHEN reservasi tanpa konflik diajukan, THE Sistem SHALL menampilkan pesan toast sukses kepada pengguna
8. THE Sistem SHALL NOT menampilkan error validasi yang mencegah pengajuan form karena konflik jadwal

### Kebutuhan 2: Algoritma Genetika Parsial yang Dioptimalkan

**User Story:** Sebagai sistem, saya ingin merelokasi hanya jadwal akademik yang berkonflik secara efisien, sehingga GA menyelesaikan konflik dengan cepat tanpa mengganggu seluruh jadwal semester.

#### Kriteria Penerimaan

1. WHEN GA_Parsial dipanggil, THE Sistem SHALL mengidentifikasi semua entri Jadwal_Akademik yang berkonflik dengan Reservasi target
2. WHEN melakukan evolusi populasi, THE Sistem SHALL hanya memodifikasi gen yang sesuai dengan mata kuliah yang berkonflik
3. WHEN melakukan evolusi populasi, THE Sistem SHALL menjaga semua entri Jadwal_Akademik yang tidak berkonflik tetap tidak berubah
4. WHEN menghitung fitness, THE Sistem SHALL menerapkan penalti 200 jika jadwal yang direlokasi masih berkonflik dengan Reservasi target
5. WHEN menghitung fitness, THE Sistem SHALL menerapkan penalti 100 jika jadwal yang direlokasi berkonflik dengan Jadwal_Akademik lain di ruangan yang sama
6. WHEN menghitung fitness, THE Sistem SHALL menerapkan penalti 100 jika jadwal yang direlokasi berkonflik dengan Jadwal_Akademik lain untuk dosen yang sama
7. WHEN menghitung fitness, THE Sistem SHALL menerapkan penalti 100 jika kapasitas ruangan tidak mencukupi untuk mata kuliah
8. WHEN menghitung fitness, THE Sistem SHALL menerapkan penalti 5 jika jadwal dimulai pada atau setelah jam 13:00
9. WHEN Skor_Fitness 1.0 tercapai, THE Sistem SHALL menyimpan jadwal yang dioptimalkan dan mengembalikan sukses
10. WHEN jumlah maksimum generasi tercapai tanpa mencapai Skor_Fitness 1.0, THE Sistem SHALL mengembalikan kegagalan
11. THE GA_Parsial SHALL menggunakan ukuran populasi 30 untuk efisiensi
12. THE GA_Parsial SHALL menggunakan maksimum 50 generasi untuk efisiensi

### Kebutuhan 3: Antarmuka Persetujuan Admin yang Ditingkatkan

**User Story:** Sebagai admin, saya ingin melihat informasi konflik yang detail sebelum menyetujui reservasi, sehingga saya dapat membuat keputusan yang tepat tentang apakah akan melanjutkan dengan resolusi konflik otomatis.

#### Kriteria Penerimaan

1. WHEN admin melihat reservasi pending dengan Catatan_Konflik, THE Sistem SHALL menampilkan detail konflik secara menonjol
2. WHEN admin melihat reservasi pending dengan konflik, THE Sistem SHALL menampilkan entri Jadwal_Akademik mana yang akan direlokasi
3. WHEN admin mengklik setuju pada reservasi tanpa konflik, THE Sistem SHALL menyetujuinya segera
4. WHEN admin mengklik setuju pada reservasi dengan konflik, THE Sistem SHALL memanggil GA_Parsial
5. WHEN GA_Parsial berhasil, THE Sistem SHALL memperbarui entri Jadwal_Akademik, menyetujui Reservasi, dan menampilkan toast sukses
6. WHEN GA_Parsial gagal, THE Sistem SHALL menolak Reservasi, memperbarui field note dengan alasan kegagalan, dan menampilkan toast error
7. THE Sistem SHALL menampilkan nama mata kuliah, hari, dan rentang waktu untuk setiap Jadwal_Akademik yang berkonflik

### Kebutuhan 4: Logging dan Monitoring

**User Story:** Sebagai administrator sistem, saya ingin log detail dari operasi GA, sehingga saya dapat melakukan debug masalah dan memelihara audit trail dari modifikasi jadwal.

#### Kriteria Penerimaan

1. WHEN GA_Parsial dipanggil, THE Sistem SHALL mencatat ID reservasi dan timestamp
2. WHEN GA_Parsial dipanggil, THE Sistem SHALL mencatat jumlah entri Jadwal_Akademik yang berkonflik yang diidentifikasi
3. WHEN GA_Parsial selesai dengan sukses, THE Sistem SHALL mencatat jumlah generasi yang diperlukan untuk menemukan solusi
4. WHEN GA_Parsial selesai dengan sukses, THE Sistem SHALL mencatat entri Jadwal_Akademik mana yang direlokasi
5. WHEN GA_Parsial selesai dengan sukses, THE Sistem SHALL mencatat nilai lama dan baru untuk setiap jadwal yang direlokasi
6. WHEN GA_Parsial gagal, THE Sistem SHALL mencatat alasan kegagalan
7. WHEN GA_Parsial gagal, THE Sistem SHALL mencatat Skor_Fitness terbaik yang dicapai
8. THE Sistem SHALL menyimpan semua log GA di file log Laravel dengan tag konteks "GA"

### Kebutuhan 5: Peningkatan Notifikasi Pengguna

**User Story:** Sebagai pengguna, saya ingin umpan balik yang jelas tentang status reservasi saya, sehingga saya memahami apa yang terjadi selanjutnya dan apakah konflik terdeteksi.

#### Kriteria Penerimaan

1. WHEN pengguna mengajukan reservasi dengan konflik yang terdeteksi, THE Sistem SHALL menampilkan toast peringatan dengan pesan "Reservasi Anda masuk dengan status menunggu peninjauan khusus admin karena terdeteksi bentrok jadwal"
2. WHEN pengguna mengajukan reservasi tanpa konflik, THE Sistem SHALL menampilkan toast sukses dengan pesan "Pengajuan berhasil! Menunggu persetujuan admin."
3. WHEN pengguna melihat daftar reservasi mereka, THE Sistem SHALL menampilkan reservasi dengan Catatan_Konflik dengan badge peringatan
4. THE Sistem SHALL NOT mencegah pengguna mengajukan reservasi berdasarkan deteksi konflik

### Kebutuhan 6: Kompatibilitas Mundur

**User Story:** Sebagai pemelihara sistem, saya ingin optimasi bekerja dengan data yang ada, sehingga tidak diperlukan migrasi data atau downtime sistem.

#### Kriteria Penerimaan

1. THE Sistem SHALL bekerja dengan skema database yang ada tanpa modifikasi
2. THE Sistem SHALL menangani reservasi yang ada dengan atau tanpa nilai Catatan_Konflik
3. THE Sistem SHALL mempertahankan kompatibilitas dengan fungsi generasi jadwal GA penuh yang ada
4. THE Sistem SHALL mempertahankan semua relasi dan constraint model yang ada

### Kebutuhan 7: Optimasi Performa

**User Story:** Sebagai sistem, saya ingin GA_Parsial dieksekusi dengan cepat, sehingga admin menerima umpan balik segera saat menyetujui reservasi.

#### Kriteria Penerimaan

1. WHEN GA_Parsial dipanggil dengan 1-2 jadwal yang berkonflik, THE Sistem SHALL selesai dalam 5 detik
2. WHEN GA_Parsial dipanggil dengan 3-5 jadwal yang berkonflik, THE Sistem SHALL selesai dalam 10 detik
3. THE GA_Parsial SHALL menggunakan ukuran populasi yang dikurangi dibandingkan dengan GA penuh
4. THE GA_Parsial SHALL menggunakan jumlah generasi yang dikurangi dibandingkan dengan GA penuh
5. THE GA_Parsial SHALL hanya mengevaluasi fitness untuk gen yang dimodifikasi

### Kebutuhan 8: Penanganan Error

**User Story:** Sebagai sistem, saya ingin menangani kasus edge dengan baik, sehingga aplikasi tetap stabil bahkan ketika situasi yang tidak terduga terjadi.

#### Kriteria Penerimaan

1. WHEN tidak ada ruangan yang tersedia untuk relokasi, THE GA_Parsial SHALL mengembalikan kegagalan dengan pesan deskriptif
2. WHEN GA_Parsial mengalami exception, THE Sistem SHALL mencatat error dan mengembalikan kegagalan
3. WHEN reservasi diajukan dengan rentang waktu yang tidak valid, THE Sistem SHALL memvalidasi dan menolak sebelum menyimpan
4. WHEN admin menyetujui reservasi yang tidak lagi memiliki konflik, THE Sistem SHALL menyetujui tanpa memanggil GA_Parsial
5. IF GA_Parsial gagal, THEN THE Sistem SHALL mempertahankan Jadwal_Akademik asli tanpa modifikasi
