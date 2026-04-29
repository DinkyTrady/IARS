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
                // Overlap: start_A < end_B AND end_A > start_B
                $query->whereTime('start_time', '<', $end->format('H:i:s'))
                      ->whereTime('end_time', '>', $start->format('H:i:s'));
            })
            ->with('course')
            ->first();

        if ($conflictSchedule) {
            $note = "Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan {$conflictSchedule->course->name} ({$conflictSchedule->start_time} - {$conflictSchedule->end_time}).";
        }

        // 2. Cek konflik dengan Reservasi lain (pending/approved)
        if (! $note) {
            $conflictReservation = Reservation::where('room_id', $this->room_id)
                ->where('date', $this->date)
                ->whereIn('status', ['approved', 'pending'])
                ->where(function ($query) use ($start, $end) {
                    // Overlap: start_A < end_B AND end_A > start_B
                    $query->whereTime('start_time', '<', $end->format('H:i:s'))
                          ->whereTime('end_time', '>', $start->format('H:i:s'));
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

<div class="space-y-6 max-w-2xl mx-auto">
    <header>
        <div class="flex items-center gap-3 mb-1">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('dashboard') }}" wire:navigate />
            <flux:heading size="xl">Buat Reservasi Ruangan</flux:heading>
        </div>
        <flux:subheading class="ml-11">Sistem akan memeriksa konflik jadwal secara otomatis sebelum reservasi diajukan.</flux:subheading>
    </header>

    <flux:separator variant="subtle" />

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="md">Detail Kegiatan</flux:heading>

            <flux:field>
                <flux:label>Nama Kegiatan</flux:label>
                <flux:input wire:model="activity_name" placeholder="Contoh: Rapat BEM, Seminar Teknik..." required />
                <flux:error name="activity_name" />
            </flux:field>

            <flux:field>
                <flux:label>Deskripsi Kegiatan <flux:badge size="sm" variant="ghost">Opsional</flux:badge></flux:label>
                <flux:textarea wire:model="description" placeholder="Jelaskan detail kegiatan Anda..." rows="3" />
                <flux:error name="description" />
            </flux:field>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="md">Ruangan & Waktu</flux:heading>

            <flux:field>
                <flux:label>Pilih Ruangan</flux:label>
                <flux:select wire:model="room_id" required>
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
                <flux:label>Tanggal</flux:label>
                <flux:input type="date" wire:model="date" min="{{ date('Y-m-d') }}" required />
                <flux:error name="date" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Jam Mulai</flux:label>
                    <flux:input type="time" wire:model="start_time" required />
                    <flux:error name="start_time" />
                </flux:field>

                <flux:field>
                    <flux:label>Jam Selesai</flux:label>
                    <flux:input type="time" wire:model="end_time" required />
                    <flux:error name="end_time" />
                </flux:field>
            </div>
        </flux:card>

        <flux:callout variant="info" icon="information-circle">
            <flux:callout.text class="text-sm">
                Reservasi Anda akan masuk ke antrian <strong>menunggu persetujuan admin</strong>. Pastikan data sudah benar sebelum diajukan.
            </flux:callout.text>
        </flux:callout>

        <div class="flex gap-3 justify-end">
            <flux:button variant="ghost" href="{{ route('dashboard') }}" wire:navigate>Batal</flux:button>
            <flux:button type="submit" variant="primary" icon="check">Ajukan Reservasi</flux:button>
        </div>
    </form>
</div>
