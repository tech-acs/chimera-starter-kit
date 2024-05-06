@props([
    'image' => null,
    'title' => '',
    'description' => '',
    'link' => '',
])
<div class="relative flex h-64 rounded-lg overflow-hidden border shadow-md">
    <img src="{{ $image }}" class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="relative flex w-full flex-col items-start justify-end bg-black bg-opacity-60 p-8 sm:p-12">
        <a href="{{ $link }}">
            <h2 class="text-2xl font-medium text-white">{{ $title }}</h2>
            <p class="mt-1 text-lg font-medium text-white">{{ $description }}</p>
        </a>
    </div>
</div>
