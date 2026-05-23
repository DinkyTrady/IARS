<?php

use App\Models\Lecturer;
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

    #[Validate('required|string|unique:lecturers,nidn')]
    public string $nidn = '';

    #[Validate('nullable|email|unique:lecturers,email')]
    public string $email = '';

    #[Validate('nullable|string')]
    public string $phone = '';

    // Edit form
    public ?int $editingId = null;
    public string $editName = '';
    public string $editNidn = '';
    public string $editEmail = '';
    public string $editPhone = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'lecturers' => Lecturer::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('nidn', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(10),
        ];
    }

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'nidn' => 'required|string|unique:lecturers,nidn',
            'email' => 'nullable|email|unique:lecturers,email',
            'phone' => 'nullable|string|max:20',
        ]);

        Lecturer::create([
            'name' => $this->name,
            'nidn' => $this->nidn,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
        ]);

        $this->reset(['name', 'nidn', 'email', 'phone']);
        $this->isCreating = false;
        Flux::toast('Dosen berhasil ditambahkan.', variant: 'success');
    }

    public function openEdit(int $id): void
    {
        $lecturer = Lecturer::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $lecturer->name;
        $this->editNidn = $lecturer->nidn;
        $this->editEmail = $lecturer->email ?? '';
        $this->editPhone = $lecturer->phone ?? '';
        Flux::modal('edit-lecturer-modal')->show();
    }

    public function update(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editNidn' => "required|string|unique:lecturers,nidn,{$this->editingId}",
            'editEmail' => "nullable|email|unique:lecturers,email,{$this->editingId}",
            'editPhone' => 'nullable|string|max:20',
        ]);

        Lecturer::findOrFail($this->editingId)->update([
            'name' => $this->editName,
            'nidn' => $this->editNidn,
            'email' => $this->editEmail ?: null,
            'phone' => $this->editPhone ?: null,
        ]);

        Flux::modal('edit-lecturer-modal')->close();
        $this->reset(['editingId', 'editName', 'editNidn', 'editEmail', 'editPhone']);
        Flux::toast('Data dosen berhasil diperbarui.', variant: 'success');
    }

    public function delete(int $id): void
    {
        Lecturer::findOrFail($id)->delete();
        Flux::toast('Dosen berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl" class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-violet-600 font-bold">Manajemen Dosen</flux:heading>
            <flux:subheading>Kelola data staf pengajar untuk penjadwalan akademik.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:input wire:model.live="search" placeholder="Cari dosen..." icon="magnifying-glass" class="w-full sm:w-64" clearable />
            <flux:button variant="primary" wire:click="$toggle('isCreating')" icon="{{ $isCreating ? 'x-mark' : 'plus' }}">
                {{ $isCreating ? 'Batal' : 'Tambah Dosen' }}
            </flux:button>
        </div>
    </header>

    @if ($isCreating)
        <flux:card class="space-y-4">
            <flux:heading size="lg">Tambah Dosen Baru</flux:heading>
            <form wire:submit="create" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="name" label="Nama Lengkap dengan Gelar" placeholder="Dr. Budi Santoso, S.Kom, M.T" required />
                    <flux:input wire:model="nidn" label="NIDN" placeholder="1234567890" required />
                    <flux:input type="email" wire:model="email" label="Email Kampus" placeholder="budi@kampus.ac.id" />
                    <flux:input type="tel" wire:model="phone" label="Nomor Telepon" placeholder="081234567890" />
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <flux:button type="button" variant="ghost" wire:click="$toggle('isCreating')">Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Dosen</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="bg-white border border-blue-100 shadow-lg shadow-blue-900/5 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/10 hover:-translate-y-0.5">
        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-violet-500 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <flux:icon.users class="text-white/80" />
                Daftar Dosen
            </h3>
        </div>
        <div class="p-2 sm:p-4 overflow-x-auto">
            <flux:table>
            <flux:table.columns>
                <flux:table.column>NIDN</flux:table.column>
                <flux:table.column>Nama Dosen</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Telepon</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($lecturers as $lecturer)
                    <flux:table.row wire:key="{{ $lecturer->id }}" class="transition-colors duration-200 hover:bg-blue-50/50">
                        <flux:table.cell><span class="font-bold font-mono text-sm">{{ $lecturer->nidn }}</span></flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $lecturer->name }}</flux:table.cell>
                        <flux:table.cell>{{ $lecturer->email ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $lecturer->phone ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button variant="ghost" size="sm" wire:click="openEdit({{ $lecturer->id }})" icon="pencil" />
                                <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                                    wire:click="delete({{ $lecturer->id }})" wire:confirm="Yakin hapus data dosen '{{ $lecturer->name }}'?"
                                    icon="trash" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-8 text-neutral-500">
                            {{ $search ? 'Tidak ada dosen yang cocok dengan pencarian.' : 'Belum ada data dosen.' }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
            </flux:table>
        </div>
        <div class="p-4 border-t border-blue-100 bg-slate-50/50">
            {{ $lecturers->links() }}
        </div>
    </div>

    {{-- Modal Edit Dosen --}}
    <flux:modal name="edit-lecturer-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Data Dosen</flux:heading>
                <flux:subheading>Perbarui data dosen yang dipilih.</flux:subheading>
            </div>
            <form wire:submit="update" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="editName" label="Nama Lengkap" required />
                    <flux:input wire:model="editNidn" label="NIDN" required />
                    <flux:input type="email" wire:model="editEmail" label="Email" />
                    <flux:input type="tel" wire:model="editPhone" label="Telepon" />
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
