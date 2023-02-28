<dl wire:init="setStats" class="relative grid grid-cols-1 content-center rounded-md border bg-white divide-y divide-gray-200 md:grid-cols-4 md:divide-y-0 md:divide-x">
    <div class="absolute z-50 left-2 bottom-2 opacity-25 cursor-pointer" title="{{ $dataTimestamp?->toDayDateTimeString() }}">
        <x-chimera::icon.stamp class="text-gray-800 w-4 h-4" />
    </div>
    <div wire:loading class="h-24 col-span-4">
        <div class="flex justify-center h-full items-center text-lg text-gray-400"><div>Fetching data . . . </div></div>
    </div>
    @foreach($stats as $name => $value)
        <div class="relative">
            <x-chimera::case-icon :type="$name" />
            <div class="p-4 sm:p-5">
                <div class="flex justify-end">
                    <dt class="text-sm font-normal text-gray-900 text-right">
                        {{ ucfirst(__($name)) }}
                    </dt>
                </div>
                <dd class="mt-1 flex justify-end items-center md:block lg:flex">
                    <div class="flex items-baseline ml-2 text-2xl font-semibold">
                        {{ (new \NumberFormatter(app()->getLocale(), \NumberFormatter::TYPE_INT32))->format($value) }}
                    </div>
                </dd>
            </div>
        </div>
    @endforeach
</dl>
