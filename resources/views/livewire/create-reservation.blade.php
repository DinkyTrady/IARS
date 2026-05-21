<?php

use App\Models\Reservation;
use App\Models\Room;
use App\Models\AcademicSchedule;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    #[Validate('required|exists:rooms,id')]
    public ?int $room_id = null;

    #[Validate('required|string|min:3|max:255')]
    public string $activity_name = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|date|after_or_equal:today')]
    public string $date = '';

    #[Validate('required|date_format:H:i')]
    public string $start_time = '';

    #[Validate('required|date_format:H:i|after:start_time')]
    public string $end_time = '';

    public function with(): array
    {
        return [
            'rooms' => Room::where('status', 'available')->orderBy('name')->get(),
            'selectedRoom' => $this->room_id ? Room::find($this->room_id) : null,
        ];
    }

    public function save(): void
    {
        $this->validate();

        $start = Carbon::createFromFormat('H:i', $this->start_time);
        $end = Carbon::createFromFormat('H:i', $this->end_time);

        $note = null;

        // 1. Cek konflik Jadwal Akademik (GA)
        $dayOfWeek = Carbon::parse($this->date)->dayOfWeekIso; // 1=Senin, 5=Jumat

        $conflictSchedule = AcademicSchedule::where('room_id', $this->room_id)
            ->where('day', $dayOfWeek)
            ->where(function ($query) use ($start, $end) {
                // Overlap: start_A < end_B AND end_A > start_B (SQLite & PostgreSQL compatible)
                $query->where('start_time', '<', $end->format('H:i:s'))
                      ->where('end_time', '>', $start->format('H:i:s'));
            })
            ->with('course')
            ->first();

        if ($conflictSchedule) {
            $note = "Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan {$conflictSchedule->course->name} ({$conflictSchedule->start_time} - {$conflictSchedule->end_time}).";
        }

        // 2. Cek konflik dengan Reservasi lain (pending/approved)
        if (! $note) {
            $conflictReservation = Reservation::where('room_id', $this->room_id)
                ->whereDate('date', $this->date)
                ->whereIn('status', ['approved', 'pending'])
                ->where(function ($query) use ($start, $end) {
                    // Overlap: start_A < end_B AND end_A > start_B (SQLite & PostgreSQL compatible)
                    $query->where('start_time', '<', $end->format('H:i:s'))
                          ->where('end_time', '>', $start->format('H:i:s'));
                })
                ->with('user') // Eager load untuk pesan yang lebih informatif
                ->first();

            if ($conflictReservation) {
                $note = "Peringatan Sistem: Ruangan bentrok dengan reservasi '{$conflictReservation->activity_name}' ({$conflictReservation->start_time} - {$conflictReservation->end_time}).";
            }
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'room_id' => $this->room_id,
            'activity_name' => $this->activity_name,
            'description' => $this->description ?: null,
            'date' => $this->date,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'status' => 'pending',
            'note' => $note,
        ]);

        if ($note) {
            Flux::toast('Pengajuan berhasil dengan catatan bentrok. Menunggu peninjauan khusus admin.', variant: 'warning');
        } else {
            Flux::toast('Pengajuan berhasil! Menunggu persetujuan admin.', variant: 'success');
        }
        
        $this->redirectRoute('reservations.index', navigate: true);
    }
}; ?>

<div class="space-y-6 max-w-6xl mx-auto px-4">
    <header class="flex items-center gap-3 mb-1">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('dashboard') }}" wire:navigate />
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Buat Reservasi Ruangan</h1>
            <p class="text-xs text-zinc-500 font-semibold mt-0.5">Sistem akan memeriksa konflik jadwal secara otomatis sebelum reservasi diajukan.</p>
        </div>
    </header>

    <flux:separator variant="subtle" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Left Column: Room Details & Specific Guidelines --}}
        <div class="lg:col-span-1 space-y-6">
            @if ($selectedRoom)
                <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-600"></div>
                    <h3 class="font-extrabold text-zinc-900 text-lg tracking-tight mb-2">{{ $selectedRoom->name }}</h3>
                    <p class="text-xs text-zinc-500 font-bold mb-4">{{ $selectedRoom->building }} · Lantai {{ $selectedRoom->floor }}</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-xs text-zinc-500 font-semibold border-t border-b border-zinc-100 py-3">
                            <flux:icon.users variant="mini" class="text-zinc-400" />
                            <span>Kapasitas: <strong class="text-zinc-800">{{ $selectedRoom->capacity }}</strong> orang</span>
                        </div>

                        @if(!empty($selectedRoom->facilities))
                            <div>
                                <h4 class="text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Fasilitas Ruangan:</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($selectedRoom->facilities as $facility)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-200 uppercase tracking-wide">
                                            {{ $facility }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-2">
                            <h4 class="text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Panduan Khusus:</h4>
                            <ul class="text-[11px] text-zinc-500 space-y-2 list-disc pl-4 font-medium">
                                <li>Pastikan estimasi jumlah peserta tidak melebihi kapasitas ruangan ({{ $selectedRoom->capacity }} orang).</li>
                                <li>Kembalikan susunan meja/kursi dan matikan AC/Proyektor setelah penggunaan.</li>
                                <li>Persetujuan memerlukan waktu maksimal 1x24 jam dari pengelola gedung.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-zinc-50 border-2 border-dashed border-zinc-200 rounded-2xl p-6 text-center text-zinc-400">
                    <flux:icon.building-office-2 class="size-10 mx-auto mb-3 text-zinc-400" />
                    <h4 class="font-extrabold text-zinc-800 text-sm">Informasi Ruangan</h4>
                    <p class="text-xs font-medium text-zinc-500 mt-1">Silakan pilih ruangan di formulir sebelah kanan untuk melihat detail kapasitas, fasilitas, dan panduan penggunaan.</p>
                </div>
            @endif

            <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-sm flex items-center gap-2 border-b border-zinc-100 pb-3 mb-3">
                    <flux:icon.information-circle class="text-blue-600" variant="mini" />
                    Catatan Alur Peminjaman
                </h3>
                <ol class="text-[11px] text-zinc-500 space-y-2.5 list-decimal pl-4 font-medium">
                    <li>Isi data kegiatan dan waktu secara lengkap dan benar.</li>
                    <li>Sistem akan melakukan deteksi bentrok otomatis. Jika terdeteksi bentrok dengan kuliah atau kegiatan lain, peringatan akan dicatat secara otomatis agar admin dapat memutuskan prioritas.</li>
                    <li>Status peminjaman dapat Anda pantau secara berkala melalui menu <strong>Reservasi Saya</strong>.</li>
                </ol>
            </div>
        </div>

        {{-- Right Column: Input Form --}}
        <div class="lg:col-span-2">
            <form wire:submit="save" class="space-y-6">
                <div class="bg-white border border-zinc-200 shadow-sm rounded-2xl overflow-hidden">
                    <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                        <h3 class="font-bold text-zinc-800 text-md flex items-center gap-2">
                            <flux:icon.document-text class="text-blue-600 size-5" />
                            Detail Kegiatan
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <flux:field>
                            <flux:label class="font-bold text-zinc-700">Nama Kegiatan</flux:label>
                            <flux:input wire:model="activity_name" placeholder="Contoh: Rapat BEM, Seminar Teknik..." class="rounded-xl border-zinc-200" required />
                            <flux:error name="activity_name" />
                        </flux:field>
            
                        <flux:field>
                            <flux:label class="font-bold text-zinc-700">Deskripsi Kegiatan <flux:badge size="sm" variant="ghost" class="text-zinc-500 font-bold ml-1">Opsional</flux:badge></flux:label>
                            <flux:textarea wire:model="description" placeholder="Jelaskan detail kegiatan Anda..." rows="3" class="rounded-xl border-zinc-200" />
                            <flux:error name="description" />
                        </flux:field>
                    </div>
                </div>

                <div class="bg-white border border-zinc-200 shadow-sm rounded-2xl overflow-hidden">
                    <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                        <h3 class="font-bold text-zinc-800 text-md flex items-center gap-2">
                            <flux:icon.clock class="text-blue-600 size-5" />
                            Ruangan & Waktu
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <flux:field>
                            <flux:label class="font-bold text-zinc-700">Pilih Ruangan</flux:label>
                            <flux:select wire:model.live="room_id" class="rounded-xl border-zinc-200" required>
                                <flux:select.option value="">-- Pilih Ruangan --</flux:select.option>
                                @foreach ($rooms as $room)
                                    <flux:select.option value="{{ $room->id }}">
                                        {{ $room->name }} — {{ $room->building }}, Lt.{{ $room->floor }} (Kapasitas: {{ $room->capacity }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="room_id" />
                        </flux:field>
            
                        <flux:field>
                            <flux:label class="font-bold text-zinc-700">Tanggal</flux:label>
                            <flux:input type="date" wire:model="date" min="{{ date('Y-m-d') }}" class="rounded-xl border-zinc-200" required />
                            <flux:error name="date" />
                        </flux:field>
            
                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label class="font-bold text-zinc-700">Jam Mulai</flux:label>
                                <flux:input type="time" wire:model="start_time" class="rounded-xl border-zinc-200" required />
                                <flux:error name="start_time" />
                            </flux:field>
            
                            <flux:field>
                                <flux:label class="font-bold text-zinc-700">Jam Selesai</flux:label>
                                <flux:input type="time" wire:model="end_time" class="rounded-xl border-zinc-200" required />
                                <flux:error name="end_time" />
                            </flux:field>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-2 border-t border-zinc-100">
                    <flux:button variant="ghost" class="font-bold cursor-pointer rounded-xl" href="{{ route('dashboard') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white border-none font-bold rounded-xl px-5 cursor-pointer shadow-sm shadow-blue-500/10">
                        Ajukan Reservasi
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
