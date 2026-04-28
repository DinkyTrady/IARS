<x-layouts::app :title="__('Optimasi Jadwal')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex-1 overflow-y-auto">
            <livewire:generate-schedule />
        </div>
    </div>
</x-layouts::app>
