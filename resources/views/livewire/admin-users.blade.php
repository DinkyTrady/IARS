<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use WithPagination;

    public string $search = '';

    // Edit form
    public ?int $editingId = null;
    public string $editName = '';
    public string $editEmail = '';
    public string $editRole = 'user';
    public string $editPassword = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'users' => User::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(15),
        ];
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->role ?? 'user';
        $this->editPassword = '';
        Flux::modal('edit-user-modal')->show();
    }

    public function update(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => "required|email|unique:users,email,{$this->editingId}",
            'editRole' => 'required|in:admin,user',
            'editPassword' => 'nullable|string|min:8',
        ]);

        $data = [
            'name' => $this->editName,
            'email' => $this->editEmail,
            'role' => $this->editRole,
        ];

        if ($this->editPassword) {
            $data['password'] = Hash::make($this->editPassword);
        }

        User::findOrFail($this->editingId)->update($data);

        Flux::modal('edit-user-modal')->close();
        $this->reset(['editingId', 'editName', 'editEmail', 'editRole', 'editPassword']);
        Flux::toast('Data pengguna berhasil diperbarui.', variant: 'success');
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            Flux::toast('Anda tidak bisa menghapus akun Anda sendiri.', variant: 'error');
            return;
        }

        User::findOrFail($id)->delete();
        Flux::toast('Pengguna berhasil dihapus.', variant: 'success');
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
        <div>
            <flux:heading size="xl">Manajemen Pengguna</flux:heading>
            <flux:subheading>Kelola akun pengguna sistem reservasi ruangan.</flux:subheading>
        </div>
        <flux:input wire:model.live="search" placeholder="Cari pengguna..." icon="magnifying-glass" class="w-full sm:w-64" clearable />
    </header>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Pengguna</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Bergabung</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row wire:key="{{ $user->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar size="sm" name="{{ $user->name }}" />
                            <span class="font-medium text-sm">{{ $user->name }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-neutral-600">{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $user->role === 'admin' ? 'blue' : 'neutral' }}" size="sm">
                            {{ $user->role === 'admin' ? 'Admin' : 'Pengguna' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-neutral-500">
                        {{ $user->created_at->format('d M Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button variant="ghost" size="sm" wire:click="openEdit({{ $user->id }})" icon="pencil" />
                            @if($user->id !== auth()->id())
                                <flux:button variant="ghost" size="sm" class="text-red-600 hover:text-red-700"
                                    wire:click="delete({{ $user->id }})"
                                    wire:confirm="Yakin hapus pengguna '{{ $user->name }}'? Semua reservasi miliknya juga akan dihapus."
                                    icon="trash" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-8 text-neutral-500">
                        {{ $search ? 'Tidak ada pengguna yang cocok.' : 'Belum ada pengguna.' }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- Modal Edit User --}}
    <flux:modal name="edit-user-modal" class="md:w-[450px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Pengguna</flux:heading>
                <flux:subheading>Perbarui data akun pengguna.</flux:subheading>
            </div>
            <form wire:submit="update" class="space-y-4">
                <flux:input wire:model="editName" label="Nama Lengkap" required />
                <flux:input type="email" wire:model="editEmail" label="Email" required />
                <flux:select wire:model="editRole" label="Role">
                    <flux:select.option value="user">Pengguna (Mahasiswa/Dosen)</flux:select.option>
                    <flux:select.option value="admin">Administrator</flux:select.option>
                </flux:select>
                <flux:input type="password" wire:model="editPassword" label="Password Baru (kosongkan jika tidak diubah)" viewable />
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
