<?php

use App\Models\Room;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public string $search = '';

    // Create form
    public bool $isCreating = false;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:50')]
    public string $code = '';

    #[Validate('required|string|max:255')]
    public string $building = '';

    #[Validate('required|integer|min:1')]
    public int $floor = 1;

    #[Validate('required|integer|min:1')]
    public int $capacity = 40;

    /** @var array<string> */
    public array $facilities = [];
    public string $facilityInput = '';

    // Edit form
    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $editName = '';

    #[Validate('required|string|max:50')]
    public string $editCode = '';

    #[Validate('required|string|max:255')]
    public string $editBuilding = '';

    #[Validate('required|integer|min:1')]
    public int $editFloor = 1;

    #[Validate('required|integer|min:1')]
    public int $editCapacity = 40;

    /** @var array<string> */
    public array $editFacilities = [];
    public string $editFacilityInput = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'rooms' => Room::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")
                ->orWhere('building', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(10),
        ];
    }

    public function addFacility(): void
    {
        $trimmed = trim($this->facilityInput);
        if ($trimmed && !in_array($trimmed, $this->facilities)) {
            $this->facilities[] = $trimmed;
        }
        $this->facilityInput = '';
    }

    public function removeFacility(string $facility): void
    {
        $this->facilities = array_values(array_filter($this->facilities, fn ($f) => $f !== $facility));
    }

    public function addEditFacility(): void
    {
        $trimmed = trim($this->editFacilityInput);
        if ($trimmed && !in_array($trimmed, $this->editFacilities)) {
            $this->editFacilities[] = $trimmed;
        }
        $this->editFacilityInput = '';
    }

    public function removeEditFacility(string $facility): void
    {
        $this->editFacilities = array_values(array_filter($this->editFacilities, fn ($f) => $f !== $facility));
    }

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:rooms,code|max:50',
            'building' => 'required|string|max:255',
            'floor' => 'required|integer|min:1',
            'capacity' => 'required|integer|min:1',
        ]);

        Room::create([
            'name' => $this->name,
            'code' => $this->code,
            'building' => $this->building,
            'floor' => $this->floor,
            'capacity' => $this->capacity,
            'facilities' => $this->facilities ?: ['AC', 'Projector'],
            'status' => 'available',
        ]);

        $this->reset(['name', 'code', 'building', 'floor', 'capacity', 'facilities', 'facilityInput']);
        $this->isCreating = false;
        Flux::toast('Ruangan berhasil ditambahkan.', variant: 'success');
    }

    public function openEdit(int $id): void
    {
        $room = Room::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $room->name;
        $this->editCode = $room->code;
        $this->editBuilding = $room->building;
        $this->editFloor = $room->floor;
        $this->editCapacity = $room->capacity;
        $this->editFacilities = $room->facilities ?? [];
        $this->editFacilityInput = '';
        Flux::modal('edit-room-modal')->show();
    }

    public function update(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editCode' => "required|string|max:50|unique:rooms,code,{$this->editingId}",
            'editBuilding' => 'required|string|max:255',
            'editFloor' => 'required|integer|min:1',
            'editCapacity' => 'required|integer|min:1',
        ]);

        Room::findOrFail($this->editingId)->update([
            'name' => $this->editName,
            'code' => $this->editCode,
            'building' => $this->editBuilding,
            'floor' => $this->editFloor,
            'capacity' => $this->editCapacity,
            'facilities' => $this->editFacilities,
        ]);

        Flux::modal('edit-room-modal')->close();
        $this->reset(['editingId', 'editName', 'editCode', 'editBuilding', 'editFloor', 'editCapacity', 'editFacilities', 'editFacilityInput']);
        Flux::toast('Ruangan berhasil diperbarui.', variant: 'success');
    }

    public function toggleStatus(int $id): void
    {
        $room = Room::findOrFail($id);
        $newStatus = $room->status === 'available' ? 'unavailable' : 'available';
        $room->update(['status' => $newStatus]);
        Flux::toast('Status ruangan diperbarui.', variant: 'success');
    }

    public function delete(int $id): void
    {
        Room::findOrFail($id)->delete();
        Flux::toast('Ruangan berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl" class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-violet-600 font-bold">Manajemen Ruangan</flux:heading>
            <flux:subheading>Kelola data ruangan yang dapat dipesan oleh pengguna.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:input wire:model.live="search" placeholder="Cari ruangan..." icon="magnifying-glass" class="w-full sm:w-64" clearable />
            <flux:button variant="primary" wire:click="$toggle('isCreating')" icon="{{ $isCreating ? 'x-mark' : 'plus' }}">
                {{ $isCreating ? 'Batal' : 'Tambah Ruangan' }}
            </flux:button>
        </div>
    </header>

    @if ($isCreating)
        <flux:card class="space-y-4">
            <flux:heading size="lg">Tambah Ruangan Baru</flux:heading>
            <form wire:submit="create" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="name" label="Nama Ruangan" placeholder="Ruang Teori A" required />
                    <flux:input wire:model="code" label="Kode Ruangan" placeholder="RT-A101" required />
                    <flux:input wire:model="building" label="Gedung" placeholder="Gedung A" required />
                    <flux:input type="number" wire:model="floor" label="Lantai" required />
                    <flux:input type="number" wire:model="capacity" label="Kapasitas (orang)" required />
                    <div>
                        <flux:label>Fasilitas</flux:label>
                        <div class="flex gap-2 mt-1">
                            <flux:input wire:model="facilityInput" placeholder="Ketik fasilitas..." wire:keydown.enter.prevent="addFacility" />
                            <flux:button type="button" wire:click="addFacility" icon="plus" />
                        </div>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach ($facilities as $facility)
                                <flux:badge wire:key="{{ $facility }}">
                                    {{ $facility }}
                                    <button type="button" wire:click="removeFacility('{{ $facility }}')" class="ml-1 text-xs opacity-60 hover:opacity-100">&times;</button>
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <flux:button type="button" variant="ghost" wire:click="$toggle('isCreating')">Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Ruangan</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="bg-white border border-blue-100 shadow-lg shadow-blue-900/5 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-0.5">
        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-violet-500 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <flux:icon.building-office-2 class="text-white/80" />
                Daftar Ruangan
            </h3>
        </div>
        <div class="p-2 sm:p-4 overflow-x-auto">
            <flux:table>
            <flux:table.columns>
                <flux:table.column>Kode</flux:table.column>
                <flux:table.column>Nama Ruangan</flux:table.column>
                <flux:table.column>Gedung / Lantai</flux:table.column>
                <flux:table.column>Kapasitas</flux:table.column>
                <flux:table.column>Fasilitas</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($rooms as $room)
                    <flux:table.row wire:key="{{ $room->id }}" class="transition-colors duration-200 hover:bg-blue-50/50">
                        <flux:table.cell><span class="font-bold font-mono text-sm">{{ $room->code }}</span></flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $room->name }}</flux:table.cell>
                        <flux:table.cell>{{ $room->building }} (Lt. {{ $room->floor }})</flux:table.cell>
                        <flux:table.cell>{{ $room->capacity }} orang</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $roomFacilities = is_array($room->facilities) ? $room->facilities : json_decode($room->facilities ?? '[]', true) ?? [];
                                @endphp
                                @forelse ($roomFacilities as $facility)
                                    <flux:badge color="blue" size="sm">{{ $facility }}</flux:badge>
                                @empty
                                    <span class="text-xs text-neutral-400">-</span>
                                @endforelse
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $room->status === 'available' ? 'green' : 'amber' }}" size="sm">
                                {{ $room->status === 'available' ? 'Tersedia' : 'Tidak Tersedia' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button variant="ghost" size="sm" wire:click="openEdit({{ $room->id }})" icon="pencil" />
                                <flux:button variant="ghost" size="sm" wire:click="toggleStatus({{ $room->id }})"
                                    icon="{{ $room->status === 'available' ? 'eye-slash' : 'eye' }}"
                                    title="{{ $room->status === 'available' ? 'Nonaktifkan' : 'Aktifkan' }}" />
                                <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                                    wire:click="delete({{ $room->id }})" wire:confirm="Yakin hapus ruangan '{{ $room->name }}'?"
                                    icon="trash" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8 text-neutral-500">
                            {{ $search ? 'Tidak ada ruangan yang cocok dengan pencarian.' : 'Belum ada data ruangan.' }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
            </flux:table>
        </div>
        <div class="p-4 border-t border-blue-100 bg-slate-50/50">
            {{ $rooms->links() }}
        </div>
    </div>

    {{-- Modal Edit Ruangan --}}
    <flux:modal name="edit-room-modal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Ruangan</flux:heading>
                <flux:subheading>Perbarui data ruangan yang dipilih.</flux:subheading>
            </div>

            <form wire:submit="update" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="editName" label="Nama Ruangan" required />
                    <flux:input wire:model="editCode" label="Kode Ruangan" required />
                    <flux:input wire:model="editBuilding" label="Gedung" required />
                    <flux:input type="number" wire:model="editFloor" label="Lantai" required />
                    <flux:input type="number" wire:model="editCapacity" label="Kapasitas (orang)" required />
                    <div>
                        <flux:label>Fasilitas</flux:label>
                        <div class="flex gap-2 mt-1">
                            <flux:input wire:model="editFacilityInput" placeholder="Tambah fasilitas..." wire:keydown.enter.prevent="addEditFacility" />
                            <flux:button type="button" wire:click="addEditFacility" icon="plus" />
                        </div>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach ($editFacilities as $facility)
                                <flux:badge wire:key="edit-{{ $facility }}">
                                    {{ $facility }}
                                    <button type="button" wire:click="removeEditFacility('{{ $facility }}')" class="ml-1 text-xs opacity-60 hover:opacity-100">&times;</button>
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
