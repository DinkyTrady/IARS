@props([
    'sidebar' => false,
])

@php
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    $textClass = $isAdmin && $sidebar ? '!text-white [&_*]:!text-white !font-bold [&_*]:!font-bold' : '';
@endphp

@if($sidebar)
    <flux:sidebar.brand name="Laravel Starter Kit" {{ $attributes }} class="{{ $textClass }}">
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-blue-600 text-white">
            <x-app-logo-icon class="size-5 fill-current text-white" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Laravel Starter Kit" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-blue-600 text-white">
            <x-app-logo-icon class="size-5 fill-current text-white" />
        </x-slot>
    </flux:brand>
@endif
