<x-layouts::app :title="__('Manajemen Ruangan')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex-1 overflow-y-auto">
            <livewire:admin.rooms />
        </div>
    </div>
</x-layouts::app>
