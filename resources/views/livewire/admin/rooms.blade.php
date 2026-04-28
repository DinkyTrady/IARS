<?php

use App\Models\Room;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public bool $isCreating = false;
    
    #[Validate('required|string|max:255')]
    public string $name = '';
    
    #[Validate('required|string|unique:rooms,code')]
    public string $code = '';
    
    #[Validate('required|string|max:255')]
    public string $building = '';
    
    #[Validate('required|integer|min:1')]
    public int $floor = 1;
    
    #[Validate('required|integer|min:1')]
    public int $capacity = 40;

    public function with(): array
    {
        return [
            'rooms' => Room::latest()->paginate(10),
        ];
    }

    public function create(): void
    {
        $this->validate();

        Room::create([
            'name' => $this->name,
            'code' => $this->code,
            'building' => $this->building,
            'floor' => $this->floor,
            'capacity' => $this->capacity,
            'facilities' => ['AC', 'Projector'],
            'status' => 'available',
        ]);

        $this->reset(['name', 'code', 'building', 'floor', 'capacity']);
        $this->isCreating = false;
        Flux::toast('Ruangan berhasil ditambahkan.', variant: 'success');
    }

    public function delete(int $id): void
    {
        Room::findOrFail($id)->delete();
        Flux::toast('Ruangan berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex justify-between items-end">
        <div>
            <flux:heading size="xl">Manajemen Ruangan</flux:heading>
            <flux:subheading>Kelola data ruangan yang dapat dipesan.</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="$toggle('isCreating')">
            {{ $isCreating ? 'Batal Tambah' : 'Tambah Ruangan' }}
        </flux:button>
    </header>

    @if ($isCreating)
        <flux:card>
            <form wire:submit="create" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="name" label="Nama Ruangan" placeholder="Ruang Teori 1" required />
                    <flux:input wire:model="code" label="Kode Ruangan" placeholder="RT-01" required />
                    <flux:input wire:model="building" label="Gedung" placeholder="Gedung A" required />
                    <flux:input type="number" wire:model="floor" label="Lantai" required />
                    <flux:input type="number" wire:model="capacity" label="Kapasitas Mahasiswa" required />
                </div>
                <div class="flex justify-end pt-2">
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Kode</flux:table.column>
            <flux:table.column>Nama Ruangan</flux:table.column>
            <flux:table.column>Gedung / Lantai</flux:table.column>
            <flux:table.column>Kapasitas</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($rooms as $room)
                <flux:table.row>
                    <flux:table.cell><span class="font-bold">{{ $room->code }}</span></flux:table.cell>
                    <flux:table.cell>{{ $room->name }}</flux:table.cell>
                    <flux:table.cell>{{ $room->building }} (Lt. {{ $room->floor }})</flux:table.cell>
                    <flux:table.cell>{{ $room->capacity }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                            wire:click="delete({{ $room->id }})" wire:confirm="Yakin hapus ruangan ini?">
                            Hapus
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $rooms->links() }}
    </div>
</div>
