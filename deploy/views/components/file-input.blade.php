@props(['disabled' => false, 'multiple' => false])

<input type="file"
    {{ $disabled ? 'disabled' : '' }}
    {{ $multiple ? 'multiple' : '' }}
    {!! $attributes->merge(['class' => "block text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100"]) !!}
/>



