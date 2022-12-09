<x-jet-dropdown align="right" width="16">
    <x-slot name="trigger">
        <x-chimera::round-button title="{{ __('Language') }}" class="px-1.5 font-medium">
            {{ str()->upper($locale) }}
        </x-chimera::round-button>
    </x-slot>

    <x-slot name="content">
        @foreach($languages as $value => $label)
            @if($locale === $value)
                <a class="px-4 cursor-pointer block px-4 py-2 text-sm leading-5 text-gray-700 bg-gray-200">{{ $label }}</a>
            @else
                <x-jet-dropdown-link class="px-4 cursor-pointer" wire:click="changeHandler('{{ $value }}')">{{ $label }}</x-jet-dropdown-link>
            @endif
        @endforeach
    </x-slot>
</x-jet-dropdown>

