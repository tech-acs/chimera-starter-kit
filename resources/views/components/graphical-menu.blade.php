@props([
    'color' => null,
    'title' => '',
    'description' => '',
    'link' => '',
])
{{--<div class="relative flex h-64 rounded-lg overflow-hidden border shadow-md">
    <img src="{{ $image }}" class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="relative flex w-full flex-col items-start justify-end bg-black bg-opacity-60 p-8 sm:p-12">
        <a href="{{ $link }}">
            <h2 class="text-2xl font-medium text-white">{{ $title }}</h2>
            <p class="mt-1 text-lg font-medium text-white">{{ $description }}</p>
        </a>
    </div>
</div>--}}

<div class="relative flex h-64 rounded-lg overflow-hidden shadow-lg transition hover:scale-105 duration-300" style="background-color: {{ $color }};">

        <a href="{{ $link }}" class="relative group flex w-full flex-col justify-center items-center text-center p-8 sm:p-12">
            <div class="rounded-full shadow-md bg-white p-3 mb-2">
                <x-chimera::icon.graphical-menu name="{{ $title }}" color="{{ $color }}" />
            </div>
            <h2 class="text-2xl font-semibold text-white uppercase">{{ $title }}</h2>
            <p class="w-2/3 mt-1 text-sm font-medium text-white">{{ $description }}</p>
        </a>

</div>
