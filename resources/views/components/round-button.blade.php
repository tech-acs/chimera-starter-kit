<button
    type="button"
    {!! $attributes->merge(['class' => "flex-shrink-0 rounded-full bg-gray-50 p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50"]) !!}
>
    {{ $slot }}
</button>
