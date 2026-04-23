<?php

use App\Models\Room;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'rooms' => Room::all(),
        ];
    }
}; ?>

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
                @foreach (json_decode($room->facilities) as $facility)
                    <flux:badge variant="outline" size="sm">{{ $facility }}</flux:badge>
                @endforeach
            </div>

            <flux:spacer />

            <flux:button variant="primary" class="w-full" href="{{ route('dashboard') }}?room={{ $room->id }}">
                Reservasi Sekarang
            </flux:button>
        </flux:card>
    @endforeach
</div>
