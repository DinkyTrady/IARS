Walkthrough: Partial GA & Pelonggaran Reservasi Pengembangan sistem telah
selesai dilakukan sesuai dengan rencana. Berikut adalah rincian fungsionalitas
baru yang ditambahkan ke dalam sistem:

1.  Pelonggaran Validasi Reservasi Sistem saat ini lebih lenient (longgar)
    terhadap bentrok jadwal di awal. Tanggung jawab diserahkan ke sistem
    persetujuan admin. Pengajuan Bebas Hambatan: Saat user mengajukan reservasi
    di ruangan dan jam yang sudah terisi jadwal (baik jadwal kuliah maupun
    reservasi lain), proses tidak akan digagalkan. Auto-Flagging: Reservasi
    tersebut tetap akan masuk ke sistem dengan status pending, namun database
    akan mencatat pesan sistem secara otomatis di kolom note (misal:
    "Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan Algoritma &
    Struktur Data (08:00 - 09:40)."). Notifikasi User: Pengguna yang submit
    akan melihat pesan toast berwarna kuning peringatan bahwa reservasi mereka
    masuk dengan status "menunggu peninjauan khusus admin".

2.  Partial Genetic Algorithm (GA Parsial) pada Approval Admin Admin tetap
    memegang kendali penuh. Jika ada reservasi yang bentrok, admin memiliki
    opsi sakti untuk menyetujuinya tanpa merusak seluruh jadwal akademik
    semester berjalan. Validasi saat Approve: Ketika tombol Setujui ditekan,
    sistem (lewat fungsi approve di Livewire) akan memanggil
    GeneticAlgorithmService::resolveConflictForReservation. Kunci & Pindahkan
    (Isolasi Mutasi): Sistem akan mengidentifikasi mata kuliah apa saja yang
    tergusur oleh reservasi ini. Sistem mengunci (freeze) semua jadwal kuliah
    lain agar tidak berubah hari, jam, atau ruangannya. Algoritma GA hanya akan
    merelokasi mata kuliah yang tergusur tersebut ke slot waktu dan ruangan
    yang kosong. Integrasi Mulus: Jika GA berhasil menemukan ruang kosong
    (mencapai fitness score 100%), jadwal akademik akan diperbarui di
    background, dan reservasi tersebut menjadi approved. Pencegahan Error: Jika
    kampus sedang overload dan tidak ada satupun ruang kosong yang bisa
    menampung kelas yang tergusur, maka reservasi akan gagal disetujui, dan
    admin akan melihat pesan error merah di pojok layar.

3.  Manfaat Arsitektur Ini TIP Dengan menggunakan Partial GA, komputasi
    algoritma menjadi sangat cepat dan efisien karena populasi gen (jadwal)
    yang harus dicari kombinasinya jauh lebih sedikit daripada saat proses
    Generate Jadwal Baru di awal semester. Selain itu, kebingungan dosen dan
    mahasiswa akibat jadwal yang berubah-ubah di tengah jalan dapat
    diminimalisir.


Bagian penjadwalan terlalu strict.  jadi ketika ada jadwal yang bentrok jangan
langsung ditolak.  namun kembalikan ke admin untuk peninjauan aproval.
selanjutnya saya ingin kamu menyesuaikam logika ga nya untuk update perihal
reservasi ini agar bisa menyesuaikan dengan reservasi yang di lakukan dengan
optimal.
