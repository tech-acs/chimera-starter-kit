@props(['disabled' => false])

<div class="mt-1 flex rounded-md shadow-sm">
    <label class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">
         {{ str(app()->getLocale())->upper() }}
    </label>
    <input type="text" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => "min-w-0 flex-1 rounded-none rounded-r-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full px-3 border-gray-300"]) !!}>
</div>



