@props(['on'])

<div x-data="{ shown: false, timeout: null, message: null }"
     x-init="@this.on('{{ $on }}', (event) => { clearTimeout(timeout); message = event.message; shown = true; timeout = setTimeout(() => { shown = false }, 3000); })"
     x-show.transition.out.opacity.duration.1500ms="shown"
     x-transition:leave.opacity.duration.1500ms
     style="display: none;"
     x-text="message"
    {{ $attributes->merge(['class' => 'text-sm text-gray-600']) }}>
</div>
