<x-layouts::app :title="__('Manajemen Dosen')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex-1 overflow-y-auto">
            <livewire:admin.lecturers />
        </div>
    </div>
</x-layouts::app>
