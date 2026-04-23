<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Selamat Datang, {{ auth()->user()->name }}!</flux:heading>
                <flux:subheading>Silakan pilih ruangan untuk reservasi kegiatan akademik atau mahasiswa.</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary">Reservasi Baru</flux:button>
        </div>

        <flux:separator variant="subtle" />

        <div class="flex-1 overflow-y-auto">
            <livewire:room-list />
        </div>
    </div>
</x-layouts::app>
