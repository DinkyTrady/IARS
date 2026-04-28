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
    public $room_id;

    #[Validate('required|string|max:255')]
    public $activity_name;

    #[Validate('nullable|string')]
    public $description;

    #[Validate('required|date|after_or_equal:today')]
    public $date;

    #[Validate('required|date_format:H:i')]
    public $start_time;

    #[Validate('required|date_format:H:i|after:start_time')]
    public $end_time;

    public function with(): array
    {
        return [
            'rooms' => Room::where('status', 'available')->get(),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        // Cek Konflik dengan Reservasi Lain yang sudah di-approve
        $conflictReservation = Reservation::where('room_id', $this->room_id)
            ->where('date', $this->date)
            ->whereIn('status', ['approved', 'pending']) // Jangan biarkan numpuk di pending juga
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->whereTime('start_time', '<', $end->format('H:i:s'))
                      ->whereTime('end_time', '>', $start->format('H:i:s'));
                });
            })
            ->first();

        if ($conflictReservation) {
            $this->addError('start_time', 'Ruangan sudah di-booking pada jam tersebut (Status: ' . $conflictReservation->status . ')');
            $this->addError('end_time', 'Ruangan sudah di-booking.');
            return;
        }

        // Cek Konflik dengan Jadwal Akademik (GA)
        $dayOfWeek = Carbon::parse($this->date)->dayOfWeekIso; // 1 = Senin, 5 = Jumat
        $conflictSchedule = AcademicSchedule::where('room_id', $this->room_id)
            ->where('day', $dayOfWeek)
            ->where(function ($query) use ($start, $end) {
                $query->whereTime('start_time', '<', $end->format('H:i:s'))
                      ->whereTime('end_time', '>', $start->format('H:i:s'));
            })
            ->first();

        if ($conflictSchedule) {
            $this->addError('start_time', 'Bentrok dengan Jadwal Perkuliahan: ' . $conflictSchedule->course->name);
            $this->addError('end_time', 'Silakan pilih jam atau ruangan lain.');
            return;
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'room_id' => $this->room_id,
            'activity_name' => $this->activity_name,
            'description' => $this->description,
            'date' => $this->date,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'status' => 'pending',
        ]);

        Flux::toast('Pengajuan pemesanan berhasil! Menunggu persetujuan admin.', variant: 'success');
        $this->redirectRoute('reservations.index');
    }
}; ?>

<div class="space-y-6">
    <header>
        <flux:heading size="xl">Buat Pesanan Ruangan</flux:heading>
        <flux:subheading>Pastikan mengecek ketersediaan jadwal agar tidak terjadi konflik.</flux:subheading>
    </header>

    <flux:separator variant="subtle" />

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <flux:input wire:model="activity_name" label="Nama Kegiatan" placeholder="Contoh: Rapat BEM" required />
        
        <flux:textarea wire:model="description" label="Deskripsi Kegiatan" placeholder="Penjelasan singkat..." />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:select wire:model="room_id" label="Pilih Ruangan" required>
                <flux:select.option value="">Pilih...</flux:select.option>
                @foreach ($rooms as $room)
                    <flux:select.option value="{{ $room->id }}">{{ $room->name }} (Kapasitas: {{ $room->capacity }})</flux:select.option>
                @endforeach
            </flux:select>
            
            <flux:input type="date" wire:model="date" label="Tanggal" required />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:input type="time" wire:model="start_time" label="Jam Mulai" required />
            <flux:input type="time" wire:model="end_time" label="Jam Selesai" required />
        </div>

        <div class="flex space-x-2 pt-4">
            <flux:button type="submit" variant="primary">Ajukan Pesanan</flux:button>
            <flux:button href="{{ route('reservations.index') }}">Batal</flux:button>
        </div>
    </form>
</div>
