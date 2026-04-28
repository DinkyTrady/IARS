<?php

use App\Models\Lecturer;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public bool $isCreating = false;
    
    #[Validate('required|string|max:255')]
    public string $name = '';
    
    #[Validate('required|string|unique:lecturers,nidn')]
    public string $nidn = '';
    
    #[Validate('nullable|email|unique:lecturers,email')]
    public string $email = '';
    
    #[Validate('nullable|string')]
    public string $phone = '';

    public function with(): array
    {
        return [
            'lecturers' => Lecturer::latest()->paginate(10),
        ];
    }

    public function create(): void
    {
        $this->validate();

        Lecturer::create([
            'name' => $this->name,
            'nidn' => $this->nidn,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->reset(['name', 'nidn', 'email', 'phone']);
        $this->isCreating = false;
        Flux::toast('Dosen berhasil ditambahkan.', variant: 'success');
    }

    public function delete(int $id): void
    {
        Lecturer::findOrFail($id)->delete();
        Flux::toast('Dosen berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex justify-between items-end">
        <div>
            <flux:heading size="xl">Manajemen Dosen</flux:heading>
            <flux:subheading>Kelola data staf pengajar.</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="$toggle('isCreating')">
            {{ $isCreating ? 'Batal Tambah' : 'Tambah Dosen' }}
        </flux:button>
    </header>

    @if ($isCreating)
        <flux:card>
            <form wire:submit="create" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="name" label="Nama Lengkap dengan Gelar" placeholder="Dr. Budi Santoso, S.Kom, M.T" required />
                    <flux:input wire:model="nidn" label="NIDN" placeholder="1234567890" required />
                    <flux:input type="email" wire:model="email" label="Email Kampus" placeholder="budi@kampus.ac.id" />
                    <flux:input type="tel" wire:model="phone" label="Nomor Telepon" placeholder="081234567890" />
                </div>
                <div class="flex justify-end pt-2">
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <flux:table>
        <flux:table.columns>
            <flux:table.column>NIDN</flux:table.column>
            <flux:table.column>Nama Dosen</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Telepon</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($lecturers as $lecturer)
                <flux:table.row>
                    <flux:table.cell><span class="font-bold">{{ $lecturer->nidn }}</span></flux:table.cell>
                    <flux:table.cell>{{ $lecturer->name }}</flux:table.cell>
                    <flux:table.cell>{{ $lecturer->email ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $lecturer->phone ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                            wire:click="delete({{ $lecturer->id }})" wire:confirm="Yakin hapus data dosen ini?">
                            Hapus
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $lecturers->links() }}
    </div>
</div>
