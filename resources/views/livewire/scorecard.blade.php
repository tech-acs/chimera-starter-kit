<div wire:init="setValue" class="flex flex-col p-3 text-center rounded-md {{ $bgColor }} shadow-sm opacity-90 relative">
    @if(! empty($scorecard->linked_indicator))
        <a href="{{ route('indicator', $scorecard->linked_indicator) }}" class="absolute right-1 top-1">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </a>
    @endif
    <div class="absolute right-1 bottom-1 opacity-60 cursor-pointer mb-1" title="{{ $dataTimestamp?->toDayDateTimeString() }}">
        <x-chimera::icon.stamp class="text-white w-3 h-3" />
    </div>
    <dt class="order-2 mt-2 text-lg leading-6 font-medium text-white">
        {{ $title }}
    </dt>
    <dd class="order-1 text-3xl font-extrabold text-white flex justify-center items-center">
        <div class="mr-2"><span wire:loading>...</span>{{ $value }}</div>
        @if (! is_null($diff))
            <x-chimera::stock-ticker diff="{{ $diff }}" diff-title="{{ $diffTitle }}" unit="{{ $unit }}"  />
        @endif
    </dd>
</div>
