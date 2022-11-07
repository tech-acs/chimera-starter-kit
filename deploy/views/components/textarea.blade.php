@props(['disabled' => false])

<div class="relative mt-1">
    <textarea
        {{ $disabled ? 'disabled' : '' }}
        {!! $attributes->merge(['class' => "w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"]) !!}
    >{{ $slot }}</textarea>
</div>



