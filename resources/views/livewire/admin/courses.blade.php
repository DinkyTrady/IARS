<?php

use App\Models\Course;
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

    #[Validate('required|string|unique:courses,code')]
    public string $code = '';

    #[Validate('required|integer|min:1|max:8')]
    public int $sks = 3;

    #[Validate('required|integer|min:1|max:8')]
    public int $semester = 1;

    #[Validate('required|integer|min:1')]
    public int $expected_students = 40;

    public ?int $lecturer_id = null;

    // Edit form
    public ?int $editingId = null;
    public string $editName = '';
    public string $editCode = '';
    public int $editSks = 3;
    public int $editSemester = 1;
    public int $editExpectedStudents = 40;
    public ?int $editLecturerId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'courses' => Course::with('academicSchedules')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(10),
            'lecturers' => Lecturer::orderBy('name')->get(),
        ];
    }

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses,code',
            'sks' => 'required|integer|min:1|max:8',
            'semester' => 'required|integer|min:1|max:8',
            'expected_students' => 'required|integer|min:1',
            'lecturer_id' => 'nullable|exists:lecturers,id',
        ]);

        Course::create([
            'name' => $this->name,
            'code' => $this->code,
            'sks' => $this->sks,
            'semester' => $this->semester,
            'expected_students' => $this->expected_students,
            'lecturer_id' => $this->lecturer_id ?: null,
        ]);

        $this->reset(['name', 'code', 'sks', 'semester', 'expected_students', 'lecturer_id']);
        $this->isCreating = false;
        Flux::toast('Mata kuliah berhasil ditambahkan.', variant: 'success');
    }

    public function openEdit(int $id): void
    {
        $course = Course::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $course->name;
        $this->editCode = $course->code;
        $this->editSks = $course->sks;
        $this->editSemester = $course->semester;
        $this->editExpectedStudents = $course->expected_students;
        $this->editLecturerId = $course->lecturer_id;
        Flux::modal('edit-course-modal')->show();
    }

    public function update(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editCode' => "required|string|unique:courses,code,{$this->editingId}",
            'editSks' => 'required|integer|min:1|max:8',
            'editSemester' => 'required|integer|min:1|max:8',
            'editExpectedStudents' => 'required|integer|min:1',
            'editLecturerId' => 'nullable|exists:lecturers,id',
        ]);

        Course::findOrFail($this->editingId)->update([
            'name' => $this->editName,
            'code' => $this->editCode,
            'sks' => $this->editSks,
            'semester' => $this->editSemester,
            'expected_students' => $this->editExpectedStudents,
            'lecturer_id' => $this->editLecturerId ?: null,
        ]);

        Flux::modal('edit-course-modal')->close();
        $this->reset(['editingId', 'editName', 'editCode', 'editSks', 'editSemester', 'editExpectedStudents', 'editLecturerId']);
        Flux::toast('Mata kuliah berhasil diperbarui.', variant: 'success');
    }

    public function delete(int $id): void
    {
        Course::findOrFail($id)->delete();
        Flux::toast('Mata kuliah berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl">Manajemen Mata Kuliah</flux:heading>
            <flux:subheading>Kelola data kurikulum dan mata kuliah untuk penjadwalan GA.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:input wire:model.live="search" placeholder="Cari mata kuliah..." icon="magnifying-glass" class="w-full sm:w-64" clearable />
            <flux:button variant="primary" wire:click="$toggle('isCreating')" icon="{{ $isCreating ? 'x-mark' : 'plus' }}">
                {{ $isCreating ? 'Batal' : 'Tambah MK' }}
            </flux:button>
        </div>
    </header>

    @if ($isCreating)
        <flux:card class="space-y-4">
            <flux:heading size="lg">Tambah Mata Kuliah Baru</flux:heading>
            <form wire:submit="create" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="name" label="Nama Mata Kuliah" placeholder="Algoritma & Pemrograman" required />
                    <flux:input wire:model="code" label="Kode MK" placeholder="IF-101" required />
                    <flux:input type="number" wire:model="sks" label="Jumlah SKS" min="1" max="8" required />
                    <flux:input type="number" wire:model="semester" label="Semester" min="1" max="8" required />
                    <flux:input type="number" wire:model="expected_students" label="Estimasi Mahasiswa" required />
                    <flux:select wire:model="lecturer_id" label="Dosen Pengampu (Opsional)">
                        <flux:select.option value="">-- Pilih Dosen --</flux:select.option>
                        @foreach ($lecturers as $lecturer)
                            <flux:select.option value="{{ $lecturer->id }}">{{ $lecturer->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <flux:button type="button" variant="ghost" wire:click="$toggle('isCreating')">Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan MK</flux:button>
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
            @forelse ($courses as $course)
                <flux:table.row wire:key="{{ $course->id }}">
                    <flux:table.cell><span class="font-bold font-mono text-sm">{{ $course->code }}</span></flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $course->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge variant="outline" size="sm">{{ $course->sks }} SKS</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>Semester {{ $course->semester }}</flux:table.cell>
                    <flux:table.cell>{{ $course->expected_students }} mhs</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button variant="ghost" size="sm" wire:click="openEdit({{ $course->id }})" icon="pencil" />
                            <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                                wire:click="delete({{ $course->id }})" wire:confirm="Yakin hapus mata kuliah '{{ $course->name }}'?"
                                icon="trash" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-8 text-neutral-500">
                        {{ $search ? 'Tidak ada MK yang cocok dengan pencarian.' : 'Belum ada data mata kuliah.' }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $courses->links() }}
    </div>

    {{-- Modal Edit MK --}}
    <flux:modal name="edit-course-modal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Mata Kuliah</flux:heading>
                <flux:subheading>Perbarui data mata kuliah yang dipilih.</flux:subheading>
            </div>
            <form wire:submit="update" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="editName" label="Nama Mata Kuliah" required />
                    <flux:input wire:model="editCode" label="Kode MK" required />
                    <flux:input type="number" wire:model="editSks" label="Jumlah SKS" min="1" max="8" required />
                    <flux:input type="number" wire:model="editSemester" label="Semester" min="1" max="8" required />
                    <flux:input type="number" wire:model="editExpectedStudents" label="Estimasi Mahasiswa" required />
                    <flux:select wire:model="editLecturerId" label="Dosen Pengampu">
                        <flux:select.option value="">-- Pilih Dosen --</flux:select.option>
                        @foreach ($lecturers as $lecturer)
                            <flux:select.option value="{{ $lecturer->id }}">{{ $lecturer->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
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
