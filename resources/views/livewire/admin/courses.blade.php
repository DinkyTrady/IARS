<?php

use App\Models\Course;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public bool $isCreating = false;
    
    #[Validate('required|string|max:255')]
    public string $name = '';
    
    #[Validate('required|string|unique:courses,code')]
    public string $code = '';
    
    #[Validate('required|integer|min:1|max:8')]
    public int $sks = 3;
    
    #[Validate('required|integer|min:1|max:8')]
    public int $semester = 1;
    
    #[Validate('required|integer|min:1')]
    public int $expected_students = 40;

    public function with(): array
    {
        return [
            'courses' => Course::latest()->paginate(10),
        ];
    }

    public function create(): void
    {
        $this->validate();

        Course::create([
            'name' => $this->name,
            'code' => $this->code,
            'sks' => $this->sks,
            'semester' => $this->semester,
            'expected_students' => $this->expected_students,
        ]);

        $this->reset(['name', 'code', 'sks', 'semester', 'expected_students']);
        $this->isCreating = false;
        Flux::toast('Mata kuliah berhasil ditambahkan.', variant: 'success');
    }

    public function delete(int $id): void
    {
        Course::findOrFail($id)->delete();
        Flux::toast('Mata kuliah berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex justify-between items-end">
        <div>
            <flux:heading size="xl">Manajemen Mata Kuliah</flux:heading>
            <flux:subheading>Kelola data kurikulum dan mata kuliah.</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="$toggle('isCreating')">
            {{ $isCreating ? 'Batal Tambah' : 'Tambah Mata Kuliah' }}
        </flux:button>
    </header>

    @if ($isCreating)
        <flux:card>
            <form wire:submit="create" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="name" label="Nama Mata Kuliah" placeholder="Algoritma Pemrograman" required />
                    <flux:input wire:model="code" label="Kode MK" placeholder="IF-101" required />
                    <flux:input type="number" wire:model="sks" label="SKS" required />
                    <flux:input type="number" wire:model="semester" label="Semester" required />
                    <flux:input type="number" wire:model="expected_students" label="Jumlah Mahasiswa (Estimasi)" required />
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
            <flux:table.column>Nama Mata Kuliah</flux:table.column>
            <flux:table.column>SKS</flux:table.column>
            <flux:table.column>Semester</flux:table.column>
            <flux:table.column>Estimasi Mhs</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($courses as $course)
                <flux:table.row>
                    <flux:table.cell><span class="font-bold">{{ $course->code }}</span></flux:table.cell>
                    <flux:table.cell>{{ $course->name }}</flux:table.cell>
                    <flux:table.cell>{{ $course->sks }}</flux:table.cell>
                    <flux:table.cell>{{ $course->semester }}</flux:table.cell>
                    <flux:table.cell>{{ $course->expected_students }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                            wire:click="delete({{ $course->id }})" wire:confirm="Yakin hapus mata kuliah ini?">
                            Hapus
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $courses->links() }}
    </div>
</div>
