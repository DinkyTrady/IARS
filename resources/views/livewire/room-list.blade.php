<?php

use App\Models\Room;
use App\Models\Reservation;
use App\Models\AcademicSchedule;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Flux\Flux;

new class extends Component {
    public ?Room $selectedRoom = null;

    #[Rule('required|string|min:3|max:255')]
    public string $activity_name = '';

    #[Rule('nullable|string|max:1000')]
    public string $description = '';

    #[Rule('required|date|after_or_equal:today')]
    public string $date = '';

    #[Rule('required')]
    public string $start_time = '';

    #[Rule('required')]
    public string $end_time = '';

    public function with(): array
    {
        return [
            'rooms' => Room::where('status', 'available')->get(),
        ];
    }

    public function selectRoom(int $roomId): void
    {
        $this->selectedRoom = Room::find($roomId);
        $this->reset(['activity_name', 'description', 'date', 'start_time', 'end_time']);
        $this->modal('reservation-modal')->show();
    }

    public function save(): void
    {
        $this->validate([
            'activity_name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $start = Carbon::parse("{$this->date} {$this->start_time}");
        $end = Carbon::parse("{$this->date} {$this->end_time}");

        $note = null;

        // 1. Cek konflik dengan Jadwal Akademik (GA) - sistem longgar, tidak langsung tolak
        $dayOfWeek = Carbon::parse($this->date)->dayOfWeekIso; // 1 = Senin, 5 = Jumat

        $academicConflict = AcademicSchedule::where('room_id', $this->selectedRoom->id)
            ->where('day', $dayOfWeek)
            ->where(function ($query) use ($start, $end) {
                // Overlap terjadi jika: start_A < end_B AND end_A > start_B
                $query->whereTime('start_time', '<', $end->format('H:i:s'))
                      ->whereTime('end_time', '>', $start->format('H:i:s'));
            })
            ->with('course')
            ->first();

        if ($academicConflict) {
            $note = "Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan {$academicConflict->course->name} ({$academicConflict->start_time} - {$academicConflict->end_time}).";
        }

        // 2. Cek konflik dengan Reservasi lain (pending/approved)
        if (! $note) {
            $reservationConflict = Reservation::where('room_id', $this->selectedRoom->id)
                ->where('date', $this->date)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($query) use ($start, $end) {
                    // Overlap terjadi jika: start_A < end_B AND end_A > start_B
                    $query->whereTime('start_time', '<', $end->format('H:i:s'))
                          ->whereTime('end_time', '>', $start->format('H:i:s'));
                })
                ->first();

            if ($reservationConflict) {
                $note = "Peringatan Sistem: Ruangan bentrok dengan reservasi '{$reservationConflict->activity_name}' ({$reservationConflict->start_time} - {$reservationConflict->end_time}).";
            }
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'room_id' => $this->selectedRoom->id,
            'activity_name' => $this->activity_name,
            'description' => $this->description ?: null,
            'date' => $this->date,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'status' => 'pending',
            'note' => $note,
        ]);

        $this->modal('reservation-modal')->close();
        $this->reset(['activity_name', 'description', 'date', 'start_time', 'end_time', 'selectedRoom']);

        if ($note) {
            Flux::toast('Pengajuan berhasil dengan catatan bentrok. Menunggu peninjauan khusus admin.', variant: 'warning');
        } else {
            Flux::toast('Reservasi berhasil diajukan! Menunggu persetujuan admin.', variant: 'success');
        }
    }
}; ?>

<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($rooms as $room)
            <flux:card wire:key="{{ $room->id }}" class="flex flex-col gap-4">
                <div class="flex justify-between items-start">
                    <div>
                        <flux:heading size="lg">{{ $room->name }}</flux:heading>
                        <flux:subheading>{{ $room->building }} — Lantai {{ $room->floor }}</flux:subheading>
                    </div>
                    <flux:badge color="green" size="sm" inset="top">Tersedia</flux:badge>
                </div>

                <div class="flex items-center gap-2 text-sm text-neutral-500">
                    <flux:icon.users variant="mini" />
                    <span>Kapasitas: {{ $room->capacity }} orang</span>
                </div>

                @if(!empty($room->facilities))
                    <div class="flex flex-wrap gap-1">
                        @foreach ($room->facilities as $facility)
                            <flux:badge variant="outline" size="sm">{{ $facility }}</flux:badge>
                        @endforeach
                    </div>
                @endif

                <flux:spacer />

                <flux:button variant="primary" class="w-full" wire:click="selectRoom({{ $room->id }})">
                    Reservasi Sekarang
                </flux:button>
            </flux:card>
        @empty
            <div class="col-span-3 flex flex-col items-center justify-center py-20 bg-neutral-50 rounded-xl border border-dashed border-neutral-300">
                <flux:icon.building-office-2 class="mb-4 text-neutral-400" size="xl" />
                <flux:heading>Tidak Ada Ruangan Tersedia</flux:heading>
                <flux:subheading>Semua ruangan sedang tidak tersedia saat ini. Hubungi admin untuk informasi lebih lanjut.</flux:subheading>
            </div>
        @endforelse
    </div>

    <flux:modal name="reservation-modal" class="md:w-[500px]">
        <div class="space-y-6">
            @if ($selectedRoom)
                <div>
                    <flux:heading size="lg">Reservasi {{ $selectedRoom->name }}</flux:heading>
                    <flux:subheading>
                        {{ $selectedRoom->building }}, Lantai {{ $selectedRoom->floor }} · Kapasitas {{ $selectedRoom->capacity }} orang
                    </flux:subheading>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>Nama Kegiatan <flux:badge size="sm" variant="outline">Wajib</flux:badge></flux:label>
                        <flux:input wire:model="activity_name" placeholder="Contoh: Rapat Himpunan, Seminar..." />
                        <flux:error name="activity_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Deskripsi Kegiatan</flux:label>
                        <flux:textarea wire:model="description" placeholder="Jelaskan detail kegiatan (opsional)..." rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal <flux:badge size="sm" variant="outline">Wajib</flux:badge></flux:label>
                        <flux:input type="date" wire:model="date" min="{{ date('Y-m-d') }}" />
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

                    <div class="flex justify-end gap-2 pt-2">
                        <flux:modal.close>
                            <flux:button variant="ghost">Batal</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="primary">
                            Ajukan Reservasi
                        </flux:button>
                    </div>
                </form>
            @endif
        </div>
    </flux:modal>
</div>
