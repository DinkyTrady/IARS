<?php

use App\Models\Room;
use App\Models\Reservation;
use App\Models\AcademicSchedule;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    public ?int $selectedRoomId = null;

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

    public function selectRoom(int $roomId): void
    {
        $this->selectedRoomId = $roomId;
        $this->reset(['activity_name', 'description', 'date', 'start_time', 'end_time']);
        $this->modal('reservation-modal')->show();
    }

    public function save(): void
    {
        $this->validate();

        $room = Room::find($this->selectedRoomId);
        if (!$room) {
            return;
        }

        $start = Carbon::parse("{$this->date} {$this->start_time}");
        $end = Carbon::parse("{$this->date} {$this->end_time}");

        $note = null;

        // 1. Cek konflik dengan Jadwal Akademik (GA)
        $dayOfWeek = Carbon::parse($this->date)->dayOfWeekIso;

        $academicConflict = AcademicSchedule::where('room_id', $room->id)
            ->where('day', $dayOfWeek)
            ->where(function ($query) use ($start, $end) {
                // Gunakan perbandingan string langsung agar sepenuhnya kompatibel dengan SQLite dan PostgreSQL
                $query->where('start_time', '<', $end->format('H:i:s'))
                      ->where('end_time', '>', $start->format('H:i:s'));
            })
            ->with('course')
            ->first();

        if ($academicConflict) {
            $note = "Peringatan Sistem: Ruangan bentrok dengan jadwal perkuliahan {$academicConflict->course->name} ({$academicConflict->start_time} - {$academicConflict->end_time}).";
        }

        // 2. Cek konflik dengan Reservasi lain (pending/approved)
        if (! $note) {
            $reservationConflict = Reservation::where('room_id', $room->id)
                ->whereDate('date', $this->date)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($query) use ($start, $end) {
                    $query->where('start_time', '<', $end->format('H:i:s'))
                          ->where('end_time', '>', $start->format('H:i:s'));
                })
                ->first();

            if ($reservationConflict) {
                $note = "Peringatan Sistem: Ruangan bentrok dengan reservasi '{$reservationConflict->activity_name}' ({$reservationConflict->start_time} - {$reservationConflict->end_time}).";
            }
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'room_id' => $room->id,
            'activity_name' => $this->activity_name,
            'description' => $this->description ?: null,
            'date' => $this->date,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'status' => 'pending',
            'note' => $note,
        ]);

        $this->modal('reservation-modal')->close();
        $this->reset(['activity_name', 'description', 'date', 'start_time', 'end_time', 'selectedRoomId']);

        if ($note) {
            Flux::toast('Pengajuan berhasil dengan catatan bentrok. Menunggu peninjauan khusus admin.', variant: 'warning');
        } else {
            Flux::toast('Reservasi berhasil diajukan! Menunggu persetujuan admin.', variant: 'success');
        }
    }
}; ?>

@php
    $cardColors = [
        ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'hover' => 'hover:border-blue-500', 'accent' => 'bg-blue-500 group-hover:bg-blue-600', 'btn_text' => 'text-white', 'btn_bg' => 'bg-blue-500', 'btn_hover' => 'hover:bg-blue-700'],
        ['bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'hover' => 'hover:border-violet-500', 'accent' => 'bg-violet-500 group-hover:bg-violet-600', 'btn_text' => 'text-white', 'btn_bg' => 'bg-violet-500', 'btn_hover' => 'hover:bg-violet-700'],
        ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'hover' => 'hover:border-emerald-500', 'accent' => 'bg-emerald-500 group-hover:bg-emerald-600', 'btn_text' => 'text-white', 'btn_bg' => 'bg-emerald-500', 'btn_hover' => 'hover:bg-emerald-700'],
        ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'hover' => 'hover:border-amber-500', 'accent' => 'bg-amber-500 group-hover:bg-amber-600', 'btn_text' => 'text-white', 'btn_bg' => 'bg-amber-500', 'btn_hover' => 'hover:bg-amber-700'],
        ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'hover' => 'hover:border-rose-500', 'accent' => 'bg-rose-500 group-hover:bg-rose-600', 'btn_text' => 'text-white', 'btn_bg' => 'bg-rose-500', 'btn_hover' => 'hover:bg-rose-700'],
    ];
@endphp

<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse ($rooms as $index => $room)
            @php
                $color = $cardColors[$index % count($cardColors)];
            @endphp
            <div wire:key="{{ $room->id }}" class="flex flex-col gap-5 border {{ $color['border'] }} {{ $color['bg'] }} p-6 rounded-[2rem] shadow-sm relative overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-xl {{ $color['hover'] }} group cursor-pointer">
                {{-- Decorative Solid Top Border Accent --}}
                <div class="absolute left-0 right-0 top-0 h-3 {{ $color['accent'] }} transition-all duration-500 group-hover:h-4"></div>

                <div class="flex justify-between items-start pt-2">
                    <div>
                        <h4 class="font-extrabold text-zinc-900 text-lg tracking-tight">{{ $room->name }}</h4>
                        <p class="text-xs text-zinc-500 font-semibold mt-0.5">{{ $room->building }} · Lantai {{ $room->floor }}</p>
                    </div>
                    <flux:badge color="green" size="sm" class="font-bold text-[10px] uppercase tracking-wider py-0.5 px-2">Tersedia</flux:badge>
                </div>

                <div class="flex items-center gap-2 text-xs text-zinc-500 font-semibold border-t border-b border-zinc-100 py-3 my-1">
                    <flux:icon.users variant="mini" class="text-zinc-400" />
                    <span>Kapasitas: <strong class="text-zinc-800 font-bold">{{ $room->capacity }}</strong> orang</span>
                </div>

                @if(!empty($room->facilities))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($room->facilities as $facility)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-200 uppercase tracking-wide">
                                {{ $facility }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <flux:spacer />

                <button 
                    type="button"
                    class="w-full border-none {{ $color['btn_bg'] }} {{ $color['btn_text'] }} {{ $color['btn_hover'] }} hover:text-white font-extrabold text-xs tracking-wide uppercase transition-all duration-300 py-3 rounded-xl cursor-pointer mt-2 shadow-sm group-hover:shadow-md" 
                    wire:click="selectRoom({{ $room->id }})"
                >
                    Reservasi Sekarang
                </button>
            </div>
        @empty
            <div class="col-span-3 flex flex-col items-center justify-center py-20 bg-zinc-50 rounded-2xl border-2 border-dashed border-zinc-200">
                <flux:icon.building-office-2 class="mb-4 text-zinc-400 size-12" />
                <h4 class="font-extrabold text-zinc-900 text-lg">Tidak Ada Ruangan Tersedia</h4>
                <p class="text-sm text-zinc-500 font-medium mt-1">Semua ruangan sedang digunakan atau dinonaktifkan saat ini.</p>
            </div>
        @endforelse
    </div>

    <flux:modal name="reservation-modal" class="md:w-[500px]">
        <div class="space-y-6">
            @if ($selectedRoomId)
                @php
                    $selectedRoom = App\Models\Room::find($selectedRoomId);
                @endphp
                @if ($selectedRoom)
                    <div>
                        <h3 class="text-xl font-extrabold text-zinc-900 tracking-tight">Reservasi {{ $selectedRoom->name }}</h3>
                        <p class="text-xs text-zinc-500 font-semibold mt-1">
                            {{ $selectedRoom->building }}, Lantai {{ $selectedRoom->floor }} · Kapasitas {{ $selectedRoom->capacity }} orang
                        </p>
                    </div>

                    <flux:separator variant="subtle" class="-my-2" />

                    <form wire:submit="save" class="space-y-4">
                        <flux:field>
                            <flux:label class="font-bold text-zinc-700">Nama Kegiatan <flux:badge size="sm" variant="ghost" class="text-red-600 bg-red-50 font-bold border-none ml-1">Wajib</flux:badge></flux:label>
                            <flux:input wire:model="activity_name" placeholder="Contoh: Rapat Himpunan, Seminar Teknik..." class="rounded-xl border-zinc-200" required />
                            <flux:error name="activity_name" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="font-bold text-zinc-700">Deskripsi Kegiatan</flux:label>
                            <flux:textarea wire:model="description" placeholder="Jelaskan detail kegiatan (opsional)..." rows="3" class="rounded-xl border-zinc-200" />
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="font-bold text-zinc-700">Tanggal <flux:badge size="sm" variant="ghost" class="text-red-600 bg-red-50 font-bold border-none ml-1">Wajib</flux:badge></flux:label>
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

                        <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                            <flux:modal.close>
                                <flux:button variant="ghost" class="font-bold cursor-pointer rounded-xl">Batal</flux:button>
                            </flux:modal.close>
                            <flux:button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white border-none font-bold rounded-xl px-5 cursor-pointer">
                                Ajukan Reservasi
                            </flux:button>
                        </div>
                    </form>
                @endif
            @endif
        </div>
    </flux:modal>
</div>
