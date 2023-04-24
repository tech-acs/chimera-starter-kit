<div wire:init="deferredLoading" class="relative z-0 px-4 py-5 sm:px-6">
    <div class="opacity-25 absolute z-50 cursor-pointer" title="{{ $dataTimestamp?->toDayDateTimeString() }}">
        <x-chimera::icon.stamp class="w-4 h-4" />
    </div>
    <div
        class="chart"
        id="{{$graphDiv}}"
        data-data='@json($this->data)'
        data-layout='@json($layout)'
        data-config='@json($config)'
        wire:ignore
    ></div>

    <div wire:loading.flex class="absolute inset-0 justify-center items-center z-10 opacity-80 bg-white">
        {{ __('Updating...') }}
        <svg class="animate-spin h-5 w-5 mr-3 ..." viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="gray" stroke-width="4"></circle>
            <path class="opacity-75"  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>
