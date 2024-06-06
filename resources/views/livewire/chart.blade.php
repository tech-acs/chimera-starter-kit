<div class="relative px-4 py-5 sm:px-6">
    @if(! $isDataReady)
        <div wire:poll.visible.3s="checkData"></div>

        @include('chimera::livewire.placeholders.chart')
    @else
        <div
            wire:ignore
            id="{{ $graphDiv }}"
            data-config='@json($config)'
            x-data
            x-init="console.log('1 - Alpine init: ', $wire.graphDiv); new PlotlyChart($wire.graphDiv)"
        ></div>
    @endif

</div>


