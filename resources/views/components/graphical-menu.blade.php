@props([
    'bgColor' => null,
    'fgColor' => null,
    'title' => '',
    'icon' => '',
    'description' => '',
    'link' => '',
])
<div class="relative flex h-64 rounded-lg overflow-hidden shadow-lg transition hover:scale-105 duration-300" style="background-color: {{ $bgColor }};">
    <a href="{{ $link }}" class="relative group flex w-full flex-col justify-center items-center text-center p-8 sm:p-12">
        <div class="rounded-full shadow-md bg-white p-3 mb-2">
            <x-chimera::icon.graphical-menu name="{{ $icon }}" color="{{ $bgColor }}" />
        </div>
        <h2 class="text-2xl font-semibold uppercase {{ $fgColor }}">{{ $title }}</h2>
        <p class="w-2/3 mt-1 text-sm font-medium {{ $fgColor }}">{{ $description }}</p>
    </a>
</div>
