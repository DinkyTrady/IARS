# BAB I PENDAHULUAN

## 1.1 Latar Belakang

Perkembangan teknologi informasi mendorong institusi pendidikan untuk mengadopsi
sistem digital dalam pengelolaan aktivitas akademik dan nonakademik. Secara ideal,
pengelolaan penggunaan ruangan kampus dilakukan secara terintegrasi, real-time, dan terpusat
agar tidak terjadi benturan antara jadwal perkuliahan dan kegiatan mahasiswa. Sistem reservasi
berbasis web dinilai mampu memberikan transparansi informasi, efisiensi pengelolaan, serta
kemudahan akses bagi civitas akademika.
Beberapa penelitian sebelumnya telah mengembangkan sistem reservasi ruangan berbasis
web dan mobile, serta sistem penjadwalan otomatis menggunakan pendekatan optimasi seperti
Genetic Algorithm. Penelitian lain juga mengintegrasikan fitur persetujuan (approval) dan
manajemen kegiatan mahasiswa dalam sistem terpisah. Hal ini menunjukkan bahwa
pengembangan sistem reservasi telah banyak dilakukan dan menjadi bagian dari State of The
Art dalam bidang sistem informasi kampus.
Namun demikian, berdasarkan analisis terhadap penelitian terdahulu, masih terdapat
Method Gap, yaitu sebagian sistem hanya berfokus pada proses pemesanan ruangan tanpa
integrasi optimalisasi jadwal secara menyeluruh. Beberapa penelitian mengembangkan
algoritma optimasi jadwal, tetapi tidak terhubung langsung dengan sistem reservasi berbasis
web yang digunakan oleh mahasiswa dan dosen. Selain itu, integrasi antara jadwal perkuliahan,
kegiatan mahasiswa, validasi konflik otomatis, dan manajemen persetujuan masih belum
dikembangkan dalam satu sistem terpadu.
Secara empiris, praktik di lapangan juga menunjukkan bahwa pengelolaan reservasi
ruangan masih dilakukan secara manual atau semi-digital melalui pencatatan administrasi atau
spreadsheet terpisah. Kondisi ini sering menimbulkan benturan jadwal, keterlambatan
informasi, serta kurang optimalnya pemanfaatan ruangan.
Berdasarkan kondisi tersebut, penelitian ini mengusulkan rancang bangun sistem informasi
reservasi ruangan kampus berbasis web yang mengintegrasikan manajemen reservasi, validasi
konflik jadwal, serta optimalisasi penggunaan ruangan sebagai bentuk Novelty Improvement
dari penelitian sebelumnya.

## 1.2 Rumusan Masalah

1. Bagaimana merancang dan membangun sistem informasi reservasi ruangan kampus
   berbasis web yang terintegrasi antara jadwal perkuliahan dan kegiatan mahasiswa?
2. Bagaimana sistem dapat melakukan validasi dan meminimalkan konflik jadwal secara
   otomatis?
3. Bagaimana penerapan sistem tersebut dapat meningkatkan optimalisasi penggunaan
   ruangan kampus?

## 1.3 Tujuan Penelitian

1. Mengembangkan sistem informasi reservasi ruangan kampus berbasis web yang terintegrasi
   dan mudah diakses.
2. Mengimplementasikan mekanisme validasi konflik dan pengelolaan jadwal yang lebih
   optimal.
3. Meningkatkan efisiensi dan efektivitas pemanfaatan fasilitas ruangan kampus.

## 1.4 Manfaat Penelitian

### 1. Bagi Mahasiswa dan Dosen

Mempermudah proses pemesanan ruangan, memperoleh informasi jadwal secara real-time,
serta mengurangi potensi benturan kegiatan akademik dan nonakademik.

### 2. Bagi Pengelola Kampus

Membantu pengelolaan fasilitas ruangan secara sistematis, terintegrasi, dan transparan
sehingga penggunaan ruangan lebih optimal.

### 3. Bagi Pengembangan Ilmu Sistem Informasi

Menjadi referensi pengembangan sistem reservasi berbasis web yang mengintegrasikan
validasi konflik dan optimalisasi jadwal sebagai bentuk improvement dari penelitian
terdahulu.

# BAB II TINJAUAN PUSTAKA

## 2.1 State of The Art

Perkembangan sistem reservasi fasilitas kampus berbasis digital telah banyak diteliti dalam
beberapa tahun terakhir. Penelitian oleh Tham Sheen Ee dkk. (2024) mengembangkan sistem
pemesanan ruang diskusi perpustakaan berbasis web yang mampu mengurangi konflik jadwal
melalui validasi otomatis dan kalender dinamis. Penelitian serupa (2023) juga menekankan
aspek usability dan pengelolaan hak akses pengguna untuk meningkatkan efektivitas sistem
reservasi.
Di sisi lain, penelitian oleh Lalitha dkk. (2023) mengembangkan sistem reservasi aula
kampus berbasis PHP dan MySQL dengan mekanisme persetujuan hierarkis untuk mencegah
double booking. Sementara itu, Zhang dkk. (2022) merancang sistem reservasi laboratorium
berbasis mobile applet yang memungkinkan pemantauan ketersediaan ruangan secara real-
time.
Penelitian yang lebih berfokus pada optimalisasi jadwal dilakukan oleh Rakhmi Khalida
dkk. (2025) melalui penerapan Genetic Algorithm untuk mengatasi benturan jadwal kuliah.
Sistem tersebut mampu menghasilkan jadwal otomatis berdasarkan perhitungan fitness untuk
meminimalkan konflik.
Selain itu, Noor Hidayah Che Lah dkk. (2024) mengembangkan sistem manajemen
kegiatan mahasiswa berbasis web untuk mendigitalisasi pencatatan aktivitas dan
mempermudah monitoring partisipasi mahasiswa.
Berdasarkan penelitian-penelitian tersebut, dapat disimpulkan bahwa sebagian besar
penelitian masih berfokus pada satu aspek tertentu, seperti reservasi ruangan, optimasi jadwal,
atau manajemen kegiatan mahasiswa secara terpisah. Belum ditemukan sistem yang
mengintegrasikan reservasi ruangan, manajemen kegiatan mahasiswa, serta optimalisasi
jadwal dalam satu platform berbasis web secara terpadu.
Dengan demikian, penelitian ini mengisi method gap dengan mengembangkan sistem
terintegrasi yang menggabungkan fitur reservasi, validasi konflik jadwal, manajemen kegiatan
mahasiswa, serta optimalisasi jadwal sebagai bentuk improvement dari penelitian sebelumnya.

## 2.2 Landasan Teori

### 2.2.1 Sistem Informasi

Sistem informasi merupakan kombinasi antara perangkat lunak, perangkat keras,
basis data, prosedur, dan sumber daya manusia yang bertujuan untuk mengolah data
menjadi informasi yang berguna dalam pengambilan keputusan.

### 2.2.2 Sistem Reservasi

Sistem reservasi adalah mekanisme pemesanan sumber daya (ruangan) yang
dilakukan secara terstruktur untuk memastikan penggunaan yang efisien dan terjadwal.
Sistem ini harus mampu melakukan validasi ketersediaan waktu dan mencegah konflik
penggunaan.

### 2.2.3 Basis Data

Basis data digunakan untuk menyimpan informasi terkait pengguna, ruangan, jadwal
perkuliahan, serta kegiatan mahasiswa. Pengelolaan basis data yang baik memungkinkan
integrasi data secara konsisten dan real-time.

### 2.2.4 Algoritma Penjadwalan (Genetic Algorithm)

Genetic Algorithm (GA) merupakan metode optimasi yang meniru proses seleksi
alam melalui tahapan seleksi, crossover, dan mutasi. Dalam konteks penjadwalan, GA
digunakan untuk mencari kombinasi jadwal terbaik berdasarkan fungsi fitness yang
meminimalkan konflik ruangan, dosen, dan waktu.
Secara umum, proses GA meliputi:

1. Inisialisasi populasi awal jadwal.
2. Evaluasi nilai fitness tiap solusi.
3. Seleksi solusi terbaik.
4. Proses crossover dan mutasi.
5. Iterasi hingga diperoleh solusi optimal.

### 2.2.5 Pengembangan Sistem Berbasis Web

Sistem berbasis web memungkinkan akses multi-user melalui jaringan internet atau
intranet kampus. Keunggulannya meliputi kemudahan akses, pemeliharaan terpusat, serta
kompatibilitas dengan berbagai perangkat

## 2.3 Kerangka Pemikiran

Permasalahan utama dalam pengelolaan ruangan kampus adalah terjadinya benturan
jadwal serta kurang optimalnya pemanfaatan fasilitas akibat sistem reservasi yang masih
manual atau tidak terintegrasi. Selain itu, kegiatan mahasiswa sering kali tidak terkoordinasi
dengan jadwal akademik sehingga berpotensi menimbulkan konflik penggunaan ruangan.
Berdasarkan teori sistem informasi dan algoritma penjadwalan, solusi yang dapat
diterapkan adalah pengembangan sistem reservasi ruangan berbasis web yang terintegrasi
dengan manajemen kegiatan mahasiswa dan dilengkapi mekanisme optimasi jadwal.
Alur logika penelitian ini adalah sebagai berikut:

1. Identifikasi permasalahan reservasi dan konflik jadwal.
2. Analisis penelitian terdahulu dan penentuan method gap.
3. Perancangan sistem terintegrasi berbasis web.
4. Implementasi fitur reservasi, validasi konflik, dan optimasi jadwal menggunakan Genetic
   Algorithm.
5. Evaluasi efektivitas sistem dalam meningkatkan optimalisasi penggunaan ruangan
   kampus.
   Dengan pendekatan deduktif, penelitian ini menerapkan teori sistem informasi dan
   optimasi penjadwalan untuk menghasilkan solusi berupa sistem reservasi terintegrasi sebagai
   bentuk improvement dari sistem yang telah ada sebelumnya
