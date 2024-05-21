<dl wire:init="setStats" class="content-center mb-2">
    <div class="text-sm font-semibold uppercase text-left text-gray-600 tracking-wider mb-1 flex align-middle">
        <div>{{ __('Interview stats') }}</div>
        <a class="cursor-pointer" title="Calculated {{ $dataTimestamp?->diffForHumans() }} ({{ $dataTimestamp?->toDayDateTimeString() }})">
            <x-chimera::icon.stamp class="text-gray-600 ml-2" />
        </a>
    </div>

    <div wire:loading class="h-16">
        <div class="flex justify-center h-full items-center text-xl text-gray-400"><div>Fetching data . . . </div></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($stats as $name => $value)
            <div class="flex rounded-md shadow bg-white p-2">
                <x-chimera::case-icon :type="$name" class="opacity-75" />
                <div class="flex-1 truncate px-4">
                    <div class="font-medium text-2xl text-gray-900 hover:text-gray-600">{{ Number::format((float)$value) }}</div>
                    <div class="text-gray-500 text-sm">{{ ucfirst(__($name)) }}</div>
                </div>
            </div>
        @endforeach
    </div>
</dl>
