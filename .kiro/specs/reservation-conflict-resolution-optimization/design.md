# Dokumen Desain: Optimasi Resolusi Konflik Reservasi

## Gambaran Umum

Desain ini mengoptimalkan sistem resolusi konflik reservasi ruangan kampus dengan fokus pada empat area utama:

1. **Pelonggaran Validasi Frontend**: Menghapus validasi yang memblokir pengajuan reservasi dengan konflik
2. **Optimasi Algoritma GA Parsial**: Meningkatkan efisiensi dan akurasi algoritma genetika untuk relokasi jadwal
3. **Peningkatan UX Admin**: Menampilkan detail konflik dan preview hasil GA sebelum approval
4. **Logging & Monitoring**: Menambahkan audit trail lengkap untuk operasi GA

Sistem akan tetap menggunakan arsitektur Laravel 12 + Livewire 4 yang ada, dengan modifikasi minimal pada komponen yang sudah ada.

## Arsitektur

### Komponen Utama

```
┌─────────────────────────────────────────────────────────────┐
│                    User Interface Layer                      │
├─────────────────────────────────────────────────────────────┤
│  • create-reservation.blade.php (Livewire Component)        │
│  • room-list.blade.php (Livewire Component)                 │
│  • admin-reservations.blade.php (Livewire Component)        │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Service Layer                             │
├─────────────────────────────────────────────────────────────┤
│  • GeneticAlgorithmService                                   │
│    - generateOptimalSchedule() [existing]                    │
│    - resolveConflictForReservation() [optimized]            │
│    - identifyConflicts() [new]                              │
│    - logGAOperation() [new]                                 │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Data Layer                                │
├─────────────────────────────────────────────────────────────┤
│  • Reservation Model                                         │
│  • AcademicSchedule Model                                    │
│  • Room Model                                                │
│  • Course Model                                              │
│  • Lecturer Model                                            │
└─────────────────────────────────────────────────────────────┘
```

### Alur Data

#### 1. Alur Pengajuan Reservasi (User)

```
User Input → Validation → Conflict Detection → Store with Note → Toast Notification
```

**Detail:**
- User mengisi form reservasi (ruangan, tanggal, waktu, kegiatan)
- Sistem melakukan validasi dasar (format, required fields)
- Sistem mendeteksi konflik dengan jadwal akademik dan reservasi lain
- Jika ada konflik: simpan dengan status "pending" + Catatan_Konflik di field note
- Jika tidak ada konflik: simpan dengan status "pending" tanpa note
- Tampilkan toast sesuai kondisi (warning untuk konflik, success untuk normal)

#### 2. Alur Approval Admin

```
Admin Review → Check Conflicts → Invoke GA (if needed) → Update Schedule → Approve/Reject
```

**Detail:**
- Admin melihat daftar reservasi pending
- Untuk reservasi dengan Catatan_Konflik, tampilkan detail konflik
- Saat admin klik "Setujui":
  - Jika tidak ada konflik: approve langsung
  - Jika ada konflik: panggil `resolveConflictForReservation()`
    - GA berhasil: update jadwal akademik, approve reservasi, toast hijau
    - GA gagal: tolak reservasi, update note dengan alasan, toast merah

## Komponen dan Interface

### 1. GeneticAlgorithmService (Optimized)

#### Method: `resolveConflictForReservation(Reservation $reservation): array`

**Return Type:** Array dengan struktur:
```php
[
    'success' => bool,
    'message' => string,
    'generations' => int|null,
    'relocated_schedules' => array|null,
    'best_fitness' => float|null
]
```

**Algoritma:**

```
1. Identifikasi konflik:
   - Ambil day_of_week dari reservation->date
   - Query AcademicSchedule yang bentrok (room sama, day sama, waktu overlap)
   - Simpan conflict_course_ids

2. Jika tidak ada konflik:
   - Return success immediately

3. Inisialisasi populasi (size: 30):
   - Clone semua jadwal akademik saat ini sebagai base_chromosome
   - Untuk setiap individu dalam populasi:
     - Copy base_chromosome
     - Randomize hanya gen yang course_id-nya ada di conflict_course_ids
     - Simpan ke population array

4. Evolusi (max 50 generasi):
   - Untuk setiap generasi:
     a. Hitung fitness untuk semua kromosom
        - Penalty 200: gen masih bentrok dengan reservation target
        - Penalty 100: bentrok ruangan dengan jadwal lain
        - Penalty 100: bentrok dosen dengan jadwal lain
        - Penalty 100: kapasitas ruangan tidak cukup
        - Penalty 5: jadwal mulai >= 13:00
        - Fitness = 1 / (1 + total_penalty)
     
     b. Jika fitness terbaik = 1.0:
        - Log hasil
        - Simpan jadwal terbaik
        - Return success
     
     c. Seleksi (tournament size 3)
     d. Crossover parsial (hanya pada gen konflik)
     e. Mutasi parsial (hanya pada gen konflik, rate 0.1)

5. Jika max generasi tercapai tanpa solusi:
   - Log kegagalan
   - Return failure dengan best_fitness
```

#### Method Baru: `identifyConflicts(Reservation $reservation): Collection`

**Purpose:** Mengidentifikasi jadwal akademik yang bentrok dengan reservasi

**Return:** Collection of AcademicSchedule models

**Algoritma:**
```php
$dayOfWeek = Carbon::parse($reservation->date)->dayOfWeekIso;
$start = strtotime($reservation->start_time);
$end = strtotime($reservation->end_time);

return AcademicSchedule::where('room_id', $reservation->room_id)
    ->where('day', $dayOfWeek)
    ->get()
    ->filter(function ($schedule) use ($start, $end) {
        $sStart = strtotime($schedule->start_time);
        $sEnd = strtotime($schedule->end_time);
        return $sStart < $end && $sEnd > $start;
    });
```

#### Method Baru: `logGAOperation(string $event, array $data): void`

**Purpose:** Mencatat operasi GA ke Laravel log

**Parameters:**
- `$event`: Jenis event (started, success, failed, generation_complete)
- `$data`: Data kontekstual (reservation_id, conflicts_count, generations, dll)

**Implementation:**
```php
Log::channel('single')->info("GA Operation: {$event}", array_merge([
    'timestamp' => now()->toIso8601String(),
    'context' => 'GA'
], $data));
```

### 2. Livewire Components

#### create-reservation.blade.php

**Modifikasi:**
- Hapus semua logika yang menampilkan error validasi untuk konflik
- Tetap deteksi konflik, tapi simpan ke field `note` bukan tampilkan error
- Update toast message sesuai requirements

**Pseudocode:**
```
function save():
    validate_basic_fields()
    
    conflicts = detect_conflicts(room_id, date, start_time, end_time)
    
    if conflicts.academic_schedule:
        note = "Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan {course_name} ({time_range})."
    else if conflicts.reservation:
        note = "Peringatan Sistem: Ruangan bentrok dengan reservasi '{activity_name}' ({time_range})."
    else:
        note = null
    
    create_reservation(status: 'pending', note: note)
    
    if note:
        toast("Reservasi Anda masuk dengan status menunggu peninjauan khusus admin karena terdeteksi bentrok jadwal", variant: 'warning')
    else:
        toast("Pengajuan berhasil! Menunggu persetujuan admin.", variant: 'success')
    
    redirect_to_reservations_index()
```

#### room-list.blade.php

**Modifikasi:**
- Hapus validasi yang menampilkan error dan return early saat konflik
- Implementasi logika yang sama dengan create-reservation

**Pseudocode:**
```
function save():
    validate_basic_fields()
    
    conflicts = detect_conflicts(selectedRoom.id, date, start_time, end_time)
    
    note = null
    if conflicts.academic_schedule:
        note = "Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan {course_name} ({time_range})."
    else if conflicts.reservation:
        note = "Peringatan Sistem: Ruangan bentrok dengan reservasi '{activity_name}' ({time_range})."
    
    create_reservation(status: 'pending', note: note)
    
    close_modal()
    
    if note:
        toast("Reservasi Anda masuk dengan status menunggu peninjauan khusus admin karena terdeteksi bentrok jadwal", variant: 'warning')
    else:
        toast("Pengajuan berhasil! Menunggu persetujuan admin.", variant: 'success')
```

#### admin-reservations.blade.php

**Modifikasi:**
- Tambahkan tampilan detail konflik untuk reservasi dengan note
- Update method `approve()` untuk menangani response dari GA yang baru
- Tambahkan indikator visual untuk reservasi dengan konflik

**Pseudocode:**
```
function approve(reservation_id):
    reservation = find_reservation(reservation_id)
    
    // Cek apakah ada konflik
    conflicts = identify_conflicts(reservation)
    
    if conflicts.isEmpty():
        // Tidak ada konflik, approve langsung
        reservation.update(status: 'approved')
        toast("Reservasi telah disetujui.", variant: 'success')
        return
    
    // Ada konflik, panggil GA
    result = genetic_algorithm_service.resolveConflictForReservation(reservation)
    
    if result.success:
        reservation.update(status: 'approved')
        toast("Reservasi disetujui. {result.relocated_schedules.count} jadwal kuliah dipindahkan.", variant: 'success')
    else:
        reservation.update(
            status: 'rejected',
            note: "Ditolak sistem: {result.message}"
        )
        toast("Gagal menyetujui. {result.message}", variant: 'error')
```

**Template Enhancement:**
```blade
@if($reservation->note && str_contains($reservation->note, 'Peringatan Sistem'))
    <flux:badge color="yellow" size="sm" icon="exclamation-triangle">
        Konflik Terdeteksi
    </flux:badge>
    
    <flux:callout variant="warning" class="mt-2">
        {{ $reservation->note }}
    </flux:callout>
@endif
```

## Model Data

### Reservation Model

**Existing Fields:**
- `id`: bigint (PK)
- `user_id`: bigint (FK)
- `room_id`: bigint (FK)
- `activity_name`: string
- `description`: text (nullable)
- `date`: date
- `start_time`: time
- `end_time`: time
- `status`: enum('pending', 'approved', 'rejected', 'canceled')
- `note`: text (nullable) ← **Digunakan untuk Catatan_Konflik**
- `created_at`: timestamp
- `updated_at`: timestamp

**Tidak ada perubahan schema**

### AcademicSchedule Model

**Existing Fields:**
- `id`: bigint (PK)
- `course_id`: bigint (FK)
- `lecturer_id`: bigint (FK)
- `room_id`: bigint (FK)
- `day`: integer (1-5, Senin-Jumat)
- `start_time`: time
- `end_time`: time
- `created_at`: timestamp
- `updated_at`: timestamp

**Tidak ada perubahan schema**

## Correctness Properties

*Property adalah karakteristik atau behavior yang harus berlaku benar di semua eksekusi sistem yang valid - pada dasarnya, pernyataan formal tentang apa yang harus dilakukan sistem. Property berfungsi sebagai jembatan antara spesifikasi yang dapat dibaca manusia dan jaminan kebenaran yang dapat diverifikasi mesin.*

### Property 1: Penerimaan Pengajuan Universal

*Untuk setiap* reservasi yang diajukan oleh pengguna (melalui form apapun), sistem harus menerima dan menyimpan pengajuan tersebut dengan status "pending", terlepas dari apakah ada konflik jadwal yang terdeteksi.

**Validates: Requirements 1.1, 1.2, 1.5**

### Property 2: Catatan Konflik Otomatis

*Untuk setiap* reservasi yang berkonflik dengan jadwal akademik atau reservasi lain yang sudah ada, field `note` harus terisi dengan pesan peringatan yang menjelaskan konflik tersebut.

**Validates: Requirements 1.3, 1.4**

### Property 3: Tidak Ada Blocking Validation

*Untuk setiap* pengajuan reservasi, sistem tidak boleh menampilkan error validasi yang mencegah form submission berdasarkan deteksi konflik jadwal.

**Validates: Requirements 1.8, 5.4**

### Property 4: Toast Notification Sesuai Kondisi

*Untuk setiap* pengajuan reservasi, jika ada konflik maka sistem harus menampilkan toast warning, jika tidak ada konflik maka sistem harus menampilkan toast success.

**Validates: Requirements 1.6, 1.7**

### Property 5: Identifikasi Konflik Lengkap

*Untuk setiap* reservasi yang diberikan ke GA Parsial, fungsi identifikasi konflik harus mengembalikan semua dan hanya jadwal akademik yang benar-benar bentrok (ruangan sama, hari sama, waktu overlap).

**Validates: Requirements 2.1**

### Property 6: Invariant Gen Non-Konflik

*Untuk setiap* eksekusi GA Parsial, semua gen (jadwal akademik) yang tidak teridentifikasi sebagai konflik harus tetap memiliki nilai yang sama persis sebelum dan sesudah evolusi.

**Validates: Requirements 2.2, 2.3**

### Property 7: Perhitungan Fitness Sesuai Constraint

*Untuk setiap* kromosom yang dievaluasi, skor fitness harus dihitung dengan formula `1 / (1 + total_penalty)` di mana penalty diterapkan sesuai aturan:
- Penalty 200 jika gen masih bentrok dengan reservasi target
- Penalty 100 jika gen bentrok ruangan dengan jadwal lain
- Penalty 100 jika gen bentrok dosen dengan jadwal lain  
- Penalty 100 jika kapasitas ruangan tidak mencukupi
- Penalty 5 jika jadwal mulai >= 13:00

**Validates: Requirements 2.4, 2.5, 2.6, 2.7, 2.8**

### Property 8: Terminasi Sukses pada Fitness Optimal

*Untuk setiap* eksekusi GA Parsial, jika ada kromosom dengan fitness score 1.0 (tidak ada penalty), maka GA harus menyimpan jadwal tersebut dan mengembalikan success sebelum mencapai generasi maksimum.

**Validates: Requirements 2.9**

### Property 9: Terminasi Gagal pada Max Generasi

*Untuk setiap* eksekusi GA Parsial, jika generasi maksimum tercapai tanpa menemukan kromosom dengan fitness 1.0, maka GA harus mengembalikan failure.

**Validates: Requirements 2.10**

### Property 10: Approval Langsung Tanpa Konflik

*Untuk setiap* reservasi pending yang tidak memiliki konflik, ketika admin menyetujui, sistem harus langsung mengubah status menjadi "approved" tanpa memanggil GA Parsial.

**Validates: Requirements 3.3, 8.4**

### Property 11: Invokasi GA pada Approval dengan Konflik

*Untuk setiap* reservasi pending yang memiliki konflik, ketika admin menyetujui, sistem harus memanggil GA Parsial untuk mencoba menyelesaikan konflik.

**Validates: Requirements 3.4**

### Property 12: Side Effects GA Sukses

*Untuk setiap* eksekusi GA Parsial yang berhasil, sistem harus melakukan tiga hal: (1) memperbarui jadwal akademik yang direlokasi di database, (2) mengubah status reservasi menjadi "approved", dan (3) menampilkan toast success.

**Validates: Requirements 3.5**

### Property 13: Side Effects GA Gagal

*Untuk setiap* eksekusi GA Parsial yang gagal, sistem harus melakukan tiga hal: (1) mengubah status reservasi menjadi "rejected", (2) memperbarui field note dengan alasan kegagalan, dan (3) menampilkan toast error.

**Validates: Requirements 3.6**

### Property 14: Logging Invokasi GA

*Untuk setiap* kali GA Parsial dipanggil, sistem harus mencatat log entry dengan context "GA" yang berisi reservation ID, timestamp, dan jumlah konflik yang diidentifikasi.

**Validates: Requirements 4.1, 4.2**

### Property 15: Logging Hasil GA Sukses

*Untuk setiap* eksekusi GA Parsial yang berhasil, sistem harus mencatat log entry yang berisi jumlah generasi yang dibutuhkan, daftar jadwal yang direlokasi, dan nilai lama serta baru untuk setiap jadwal.

**Validates: Requirements 4.3, 4.4, 4.5**

### Property 16: Logging Hasil GA Gagal

*Untuk setiap* eksekusi GA Parsial yang gagal, sistem harus mencatat log entry yang berisi alasan kegagalan dan fitness score terbaik yang dicapai.

**Validates: Requirements 4.6, 4.7**

### Property 17: Format Log Konsisten

*Untuk setiap* log entry yang dibuat oleh GA operations, entry tersebut harus disimpan di Laravel log file dengan tag context "GA".

**Validates: Requirements 4.8**

### Property 18: Robustness terhadap Data Legacy

*Untuk setiap* reservasi yang ada di database (baik yang memiliki nilai note maupun tidak), sistem harus dapat memproses reservasi tersebut tanpa error.

**Validates: Requirements 6.2**

### Property 19: Kompatibilitas Fungsi GA Penuh

*Untuk setiap* eksekusi fungsi `generateOptimalSchedule()` (GA penuh), fungsi tersebut harus tetap bekerja dengan behavior yang sama seperti sebelum optimasi.

**Validates: Requirements 6.3**

### Property 20: Error Handling dengan Graceful Failure

*Untuk setiap* exception yang terjadi dalam GA Parsial, sistem harus menangkap exception tersebut, mencatat error ke log, dan mengembalikan failure response tanpa crash.

**Validates: Requirements 8.2**

### Property 21: Validasi Input Reservasi

*Untuk setiap* pengajuan reservasi dengan rentang waktu invalid (end_time <= start_time), sistem harus menolak pengajuan sebelum menyimpan ke database.

**Validates: Requirements 8.3**

### Property 22: Atomicity GA Operation

*Untuk setiap* eksekusi GA Parsial yang gagal, tidak boleh ada perubahan apapun pada jadwal akademik di database (rollback lengkap).

**Validates: Requirements 8.5**

## Penanganan Error

### Error Scenarios

1. **Tidak Ada Slot Kosong untuk Relokasi**
   - **Kondisi:** Semua ruangan dan waktu yang tersedia sudah terisi
   - **Handling:** GA mengembalikan failure dengan message "Tidak ada slot kosong untuk memindahkan jadwal yang tergusur"
   - **User Impact:** Admin melihat toast error, reservasi ditolak otomatis

2. **Exception dalam GA Execution**
   - **Kondisi:** Error runtime (null pointer, array out of bounds, dll)
   - **Handling:** Try-catch di level service, log error detail, return failure
   - **User Impact:** Admin melihat toast error generik, reservasi tidak diproses

3. **Invalid Time Range**
   - **Kondisi:** User submit dengan end_time <= start_time
   - **Handling:** Laravel validation menolak sebelum save
   - **User Impact:** Form validation error ditampilkan

4. **Database Transaction Failure**
   - **Kondisi:** Gagal save jadwal akademik yang direlokasi
   - **Handling:** Database transaction rollback, GA return failure
   - **User Impact:** Admin melihat toast error, tidak ada perubahan data

### Error Response Format

Semua method yang bisa gagal mengembalikan array dengan struktur:

```php
[
    'success' => false,
    'message' => 'Deskripsi error yang user-friendly',
    'error_code' => 'ERROR_CODE_CONSTANT',
    'debug_info' => [...] // Hanya di development
]
```

### Logging Strategy

- **Level INFO:** Invokasi GA, hasil sukses
- **Level WARNING:** GA gagal menemukan