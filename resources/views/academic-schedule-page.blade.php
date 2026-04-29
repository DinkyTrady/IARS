<x-layouts::app :title="__('Jadwal Perkuliahan')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex-1 overflow-y-auto">
            <livewire:academic-schedule />
        </div>
    </div>
</x-layouts::app>
