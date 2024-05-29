<div class="relative px-4 py-5 sm:px-6">
    <div
        wire:ignore
        id="{{ $graphDiv }}"
        x-data
        x-init="console.log('1 - Alpine init: ', $wire.graphDiv); new PlotlyChart($wire.graphDiv)"
    ></div>

    <div wire:loading.flex class="flex-col absolute inset-0 justify-center items-center z-10 opacity-80 bg-white">
        <svg class="block animate-spin size-10 mb-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ __('Fetching data...') }}
    </div>
</div>


