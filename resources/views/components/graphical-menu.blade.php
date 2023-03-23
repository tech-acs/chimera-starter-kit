@props([
    'image' => null,
    'title' => '',
    'description' => '',
    'link' => '',
])
<div class="group aspect-w-1 aspect-h-1 overflow-hidden rounded-lg sm:aspect-h-[1/2] sm:aspect-w-1">
    <img src="{{ $image }}" alt="" class="object-cover object-center group-hover:opacity-75">
    <div aria-hidden="true" class="bg-gradient-to-b from-transparent to-black opacity-50"></div>
    <div class="flex items-end p-6">
        <div class="bg-black/[.4] p-2 text-2xl">
            <h3 class="font-semibold text-white">
                <a href="{{ $link }}">
                    <span class="absolute inset-0"></span>
                    {{ $title }}
                </a>
            </h3>
            <p aria-hidden="true" class="mt-1 text-base text-white">{{ $description }}</p>
        </div>
    </div>
</div>
