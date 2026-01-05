<div class="relative px-4 py-5 sm:px-6"
     x-data="{ tracesStatus: $wire.entangle('dataStatus') }"
     x-init="new PlotlyChart($wire.graphDiv)"
>
    <div x-show="tracesStatus == 'pending'" x-cloak>
        <div wire:poll.visible.3s="checkData"></div>

        @include('chimera::livewire.placeholders.chart')
    </div>

    <div
        x-cloak
        x-show="tracesStatus == 'renderable'"
        x-transition.enter.duration.1000ms
        x-transition.leave.duration.150ms
    >
        <div class="opacity-25 absolute z-20 cursor-pointer" title="Calculated {{ $dataTimestamp?->diffForHumans() }} ({{ $dataTimestamp?->toDayDateTimeString() }})">
            <x-chimera::icon.stamp class="w-4 h-4" />
        </div>
        <div
            wire:ignore
            id="{{ $graphDiv }}"
            data-config='@json($config)'
        ></div>
    </div>

    <div
        x-show="tracesStatus == 'empty'" x-cloak x-transition.duration.500ms
        class="flex min-h-96 justify-center items-center text-4xl text-gray-600 z-60 opacity-90 bg-white px-4 py-5 sm:px-6"
    >
        {{ __('There is no data to display at this level') }}
    </div>

    <div
        x-show="tracesStatus == 'inapplicable'" x-cloak x-transition.duration.500ms
        class="flex min-h-96 justify-center items-center text-4xl text-gray-600 z-60 opacity-90 bg-white px-4 py-5 sm:px-6"
    >
        {{ __('The current level is inapplicable to this indicator') }}
    </div>
</div>


