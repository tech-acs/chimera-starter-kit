@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-sm text-red-600']) }}>{{ $message }}</p>
@else
    <p {{ $attributes->merge(['class' => 'text-sm text-gray-400']) }}>
        {{ $slot }}
    </p>
@enderror
