@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-left space-y-1.5 mb-2">
    <flux:heading size="xl" class="font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $title }}</flux:heading>
    <flux:subheading class="text-zinc-500 dark:text-zinc-400 font-medium">{{ $description }}</flux:subheading>
</div>
