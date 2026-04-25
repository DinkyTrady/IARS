<?php

use App\Models\Room;
use App\Models\Reservation;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Flux\Flux;

new class extends Component {
    public ?Room $selectedRoom = null;
    
    #[Rule('required|string|min:3')]
    public string $activity_name = '';

    #[Rule('required|string')]
    public string $description = '';

    #[Rule('required|date|after_or_equal:today')]
    public string $date = '';

    #[Rule('required')]
    public string $start_time = '';

    #[Rule('required|after:start_time')]
    public string $end_time = '';

    public function with(): array
    {
        return [
            'rooms' => Room::all(),
        ];
    }

    public function selectRoom(int $roomId): void
    {
        $this->selectedRoom = Room::find($roomId);
        $this->modal('reservation-modal')->show();
    }

    public function save(): void
    {
        $this->validate();

        // 1. Check for conflicts with Academic Schedule (Fixed Classes)
        $dayOfWeek = date('N', strtotime($this->date)); // 1 (Monday) to 7 (Sunday)
        
        $academicConflict = \App\Models\AcademicSchedule::where('room_id', $this->selectedRoom->id)
            ->where('day', $dayOfWeek)
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                      ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                      ->orWhere(function ($q) {
                          $q->where('start_time', '<=', $this->start_time)
                            ->where('end_time', '>=', $this->end_time);
                      });
            })
            ->exists();

        if ($academicConflict) {
            $this->addError('start_time', 'Ruangan sedang digunakan untuk jadwal perkuliahan tetap.');
            return;
        }

        // 2. Check for conflicts with other Reservations
        $reservationConflict = Reservation::where('room_id', $this->selectedRoom->id)
            ->where('date', $this->date)
            ->where('status', '!=', 'rejected') // Ignore rejected reservations
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                      ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                      ->orWhere(function ($q) {
                          $q->where('start_time', '<=', $this->start_time)
                            ->where('end_time', '>=', $this->end_time);
                      });
            })
            ->exists();

        if ($reservationConflict) {
            $this->addError('start_time', 'Ruangan sudah dipesan pada waktu tersebut.');
            return;
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'room_id' => $this->selectedRoom->id,
            'activity_name' => $this->activity_name,
            'description' => $this->description,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => 'pending',
        ]);

        $this->modal('reservation-modal')->close();
        
        $this->reset(['activity_name', 'description', 'date', 'start_time', 'end_time', 'selectedRoom']);
        
        Flux::toast(
            text: 'Reservasi berhasil diajukan dan menunggu persetujuan admin.',
            variant: 'success',
        );
    }
}; ?>

<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($rooms as $room)
            <flux:card class="flex flex-col gap-4">
                <div class="flex justify-between items-start">
                    <div>
                        <flux:heading size="lg">{{ $room->name }}</flux:heading>
                        <flux:subheading>{{ $room->building }} - Lantai {{ $room->floor }}</flux:subheading>
                    </div>
                    <flux:badge color="{{ $room->status === 'available' ? 'green' : 'red' }}" size="sm" inset="top">{{ ucfirst($room->status) }}</flux:badge>
                </div>

                <div class="flex items-center gap-2 text-sm text-neutral-500">
                    <flux:icon.users variant="mini" />
                    <span>Kapasitas: {{ $room->capacity }} orang</span>
                </div>

                <div class="flex flex-wrap gap-1">
                    @foreach ($room->facilities as $facility)
                        <flux:badge variant="outline" size="sm">{{ $facility }}</flux:badge>
                    @endforeach
                </div>

                <flux:spacer />

                <flux:button variant="primary" class="w-full" wire:click="selectRoom({{ $room->id }})">
                    Reservasi Sekarang
                </flux:button>
            </flux:card>
        @endforeach
    </div>

    <flux:modal name="reservation-modal" class="md:w-[500px]">
        <div class="space-y-6">
            @if ($selectedRoom)
                <div>
                    <flux:heading size="lg">Reservasi {{ $selectedRoom->name }}</flux:heading>
                    <flux:subheading>Isi detail kegiatan untuk mengajukan peminjaman ruangan.</flux:subheading>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>Nama Kegiatan</flux:label>
                        <flux:input wire:model="activity_name" placeholder="Contoh: Rapat Himpunan" />
                        <flux:error name="activity_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Deskripsi</flux:label>
                        <flux:textarea wire:model="description" placeholder="Jelaskan detail kegiatan..." />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal</flux:label>
                        <flux:input type="date" wire:model="date" />
                        <flux:error name="date" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Jam Mulai</flux:label>
                            <flux:input type="time" wire:model="start_time" />
                            <flux:error name="start_time" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Jam Selesai</flux:label>
                            <flux:input type="time" wire:model="end_time" />
                            <flux:error name="end_time" />
                        </flux:field>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="ghost">Batal</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="primary">Ajukan Reservasi</flux:button>
                    </div>
                </form>
            @endif
        </div>
    </flux:modal>
</div>
