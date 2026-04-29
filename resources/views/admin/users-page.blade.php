<x-layouts::app :title="__('Manajemen Pengguna')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex-1 overflow-y-auto">
            <livewire:admin-users />
        </div>
    </div>
</x-layouts::app>
