@props(['disabled' => false])

<div class="mt-1 relative rounded-md shadow-sm">
    <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
        <x-language-icon class="text-gray-400" />
    </div>
    <input type="text" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => "border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full pl-9 pr-12 border-gray-300 rounded-md"]) !!}>
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <span class="text-gray-500 sm:text-sm"> {{ str(app()->getLocale())->upper() }} </span>
    </div>
</div>



