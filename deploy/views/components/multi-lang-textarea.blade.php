@props(['disabled' => false])

<div class="relative mt-1">
    <label class="py-1 pr-3 absolute inline-flex items-center bg-transparent border-r border-b border-gray-300 px-3 text-gray-500 sm:text-sm">
        {{ str(app()->getLocale())->upper() }}
    </label>
    <textarea
        {{ $disabled ? 'disabled' : '' }}
        {!! $attributes->merge(['class' => "indent-10 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"]) !!}
    >{{ $slot }}</textarea>
</div>



