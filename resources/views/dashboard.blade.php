<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">Selamat Datang, {{ auth()->user()->name }}!</flux:heading>
                <flux:subheading>
                    @if(auth()->user()->role === 'admin')
                        Panel kontrol sistem informasi reservasi ruangan kampus.
                    @else
                        Pilih ruangan yang tersedia untuk mengajukan reservasi kegiatan Anda.
                    @endif
                </flux:subheading>
            </div>
            @if(auth()->user()->role !== 'admin')
                <flux:button icon="plus" variant="primary" href="{{ route('reservations.create') }}" wire:navigate>
                    Reservasi Baru
                </flux:button>
            @endif
        </div>

        {{-- Stats Cards (Admin Only) --}}
        @if(auth()->user()->role === 'admin')
            <livewire:admin-dashboard-stats />
            <flux:separator variant="subtle" />
        @endif

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto">
            @if(auth()->user()->role !== 'admin')
                <livewire:mahasiswa-dashboard />
            @else
                <livewire:admin-recent-reservations />
            @endif
        </div>
    </div>
</x-layouts::app>
