<div class="relative grid grid-cols-1 grid-rows-1" x-data="{ status: $wire.entangle('dataStatus') }" x-init="dataStatus = 'pending'">

    <div x-show="status == 'pending'" x-cloak class="col-start-1 row-start-1">
        <div wire:poll.visible.2s="checkData"></div>

        @include('chimera::livewire.placeholders.case-stats')
    </div>

    <div x-show="status == 'renderable'" x-cloak x-transition.duration.1000ms x-transition.leave.duration.150ms class="col-start-1 row-start-1">
        <div class="mb-2">
            <div class="text-sm font-semibold uppercase text-left text-gray-600 tracking-wider mb-1 flex align-middle ">
                <div>{{ __('Interview stats') }}</div>
                <a class="cursor-pointer" title="Calculated {{ $dataTimestamp?->diffForHumans() }} ({{ $dataTimestamp?->toDayDateTimeString() }})">
                    <x-chimera::icon.stamp class="text-gray-600 ml-2" />
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($stats ?? [] as $name => $value)
                    <div class="flex rounded-md shadow bg-white p-2 print:border">
                        <x-chimera::case-icon :type="$name" class="opacity-75" />
                        <div class="flex-1 truncate px-4">
                            <div class="font-medium text-2xl text-gray-900 hover:text-gray-600">{{ Number::format((float)$value) }}</div>
                            <div class="text-gray-500 text-sm">{{ ucfirst(__($name)) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div x-show="status == 'empty'" x-cloak x-transition.duration.500ms class="col-start-1 row-start-1 mb-2">
        <div class="text-sm font-semibold uppercase text-left text-gray-600 tracking-wider mb-1 flex align-middle ">
            <div>{{ __('Interview stats') }}</div>
        </div>
        <div class="flex justify-center items-center text-xl border rounded text-gray-600 z-60 opacity-90 bg-white px-4 py-5 sm:px-6">
            {{ __('Interview stats are not available at this time') }}
        </div>
    </div>

</div>
