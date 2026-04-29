# Fitur Jadwal Reservasi

## 📋 Deskripsi
Halaman untuk menampilkan semua reservasi ruangan yang telah disetujui (approved) dengan berbagai filter untuk memudahkan pencarian.

## 🎯 Fitur Utama

### 1. **Statistik Dashboard**
- **Reservasi Hari Ini**: Jumlah reservasi yang disetujui untuk hari ini
- **Minggu Ini**: Total reservasi minggu berjalan
- **Minggu Depan**: Total reservasi minggu depan

### 2. **Filter Pencarian**
- **Filter Ruangan**: Pilih ruangan spesifik atau lihat semua
- **Filter Periode**:
  - Minggu Ini (default)
  - Minggu Depan
  - Semua / Tanggal Spesifik
- **Tanggal Spesifik**: Muncul saat memilih "Semua" di filter periode

### 3. **Tampilan Data**
Tabel menampilkan:
- **Tanggal & Waktu**: Format Indonesia (Senin, 29 April 2026) dengan badge "Hari Ini" atau "Besok"
- **Ruangan**: Nama ruangan, gedung, dan lantai
- **Kegiatan**: Nama kegiatan yang direservasi
- **Pemohon**: Nama dan email user yang mengajukan
- **Deskripsi**: Detail kegiatan

### 4. **Fitur Tambahan**
- **Reset Filter**: Tombol untuk mengembalikan filter ke default
- **Pagination**: Menampilkan 20 data per halaman
- **Real-time Update**: Menggunakan Livewire untuk update otomatis
- **Responsive Design**: Tampilan optimal di desktop dan mobile

## 🚀 Cara Mengakses

### Dari Sidebar
Klik menu **"Jadwal Reservasi"** di sidebar (icon kalender)

### Dari URL
```
/reservations/schedule
```

## 🔐 Akses
- ✅ **User** (Mahasiswa/Dosen): Dapat melihat semua jadwal reservasi yang approved
- ✅ **Admin**: Dapat melihat semua jadwal reservasi yang approved

## 📊 Use Case

### Skenario 1: Cek Ketersediaan Ruangan Hari Ini
1. Buka halaman "Jadwal Reservasi"
2. Filter otomatis menampilkan "Minggu Ini"
3. Lihat reservasi dengan badge "Hari Ini"

### Skenario 2: Cari Reservasi di Ruangan Tertentu
1. Pilih ruangan dari dropdown "Filter Ruangan"
2. Sistem otomatis filter data
3. Lihat semua reservasi di ruangan tersebut

### Skenario 3: Cek Jadwal Tanggal Spesifik
1. Ubah "Filter Periode" ke "Semua / Tanggal Spesifik"
2. Pilih tanggal dari date picker
3. Lihat reservasi di tanggal tersebut

## 🎨 UI Components

### Stats Cards
```
┌─────────────────────────────────────┐
│  📅  5                              │
│      Reservasi Hari Ini             │
└─────────────────────────────────────┘
```

### Filter Section
```
┌─────────────────┬─────────────────┬─────────────────┐
│ Filter Ruangan  │ Filter Periode  │ Tanggal         │
│ [Dropdown]      │ [Dropdown]      │ [Date Picker]   │
└─────────────────┴─────────────────┴─────────────────┘
```

### Table View
```
┌──────────────────┬──────────┬──────────┬──────────┬──────────┐
│ Tanggal & Waktu  │ Ruangan  │ Kegiatan │ Pemohon  │ Deskripsi│
├──────────────────┼──────────┼──────────┼──────────┼──────────┤
│ Senin, 29 Apr    │ R.101    │ Rapat    │ John Doe │ Meeting  │
│ 🕐 10:00-12:00   │ Gedung A │ BEM      │ john@... │ ...      │
│ [Hari Ini]       │ Lt. 1    │          │          │          │
└──────────────────┴──────────┴──────────┴──────────┴──────────┘
```

## 🔧 Technical Details

### Route
```php
Route::view('reservations/schedule', 'reservation-schedule-page')
    ->name('reservations.schedule');
```

### Livewire Component
```
resources/views/livewire/reservation-schedule.blade.php
```

### Page View
```
resources/views/reservation-schedule-page.blade.php
```

### Query Optimization
- Eager loading: `with(['user', 'room'])`
- Pagination: 20 items per page
- Indexed queries: `status`, `date`, `room_id`

## 📝 Notes

- Hanya menampilkan reservasi dengan status **"approved"**
- Default filter: Minggu ini
- Format tanggal: Bahasa Indonesia
- Badge "Hari Ini" dan "Besok" untuk highlight
- Responsive untuk mobile dan desktop

## 🐛 Troubleshooting

### Tidak Ada Data Muncul
- Pastikan ada reservasi dengan status "approved"
- Cek filter yang aktif
- Coba reset filter

### Tanggal Tidak Sesuai
- Pastikan timezone server sudah benar
- Cek konfigurasi `config/app.php` → `timezone`

### Filter Tidak Bekerja
- Clear cache: `php artisan cache:clear`
- Rebuild views: `php artisan view:clear`
