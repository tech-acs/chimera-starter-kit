<div class="bg-white rounded-sm border-gray-200 border">
    @if (! empty($dropdowns))
        <div class="p-2 pb-4 md:flex md:space-x-4 lg:space-x-8 xl:space-x-10 items-baseline space-y-4 md:space-y-2" x-data="{ loading: false }">
            {{--@foreach($areas as $type => $area)--}}
            @foreach ($dropdowns as $levelName => $dropdown)
                <div class="flex items-baseline space-x-4">
                    <span class="text-base leading-6 font-medium text-gray-900">{{ ucfirst(__($levelName)) }}</span>
                    <div class="relative">
                        <select
                            id="{{ $levelName }}"
                            name="{{ $levelName }}"
                            wire:change="changeHandler('{{ $levelName }}', $event.target.value);"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        >
                            <option value="" @selected(empty($dropdown['selected']))>Allow all {{str($levelName)->plural()}}</option>
                            @foreach($dropdown['list'] as $path => $areaName)
                                <option
                                    value="{{ $path }}"
                                    @selected($path === ($dropdown['selected'] ?? null))
                                >{{ $areaName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach

            <div class="flex items-baseline space-x-4">
                <x-button wire:click.prevent="filter" wire:loading.attr="disabled" wire:target="filter, changeHandler">
                    {{ __('Apply') }}
                </x-button>
            </div>
        </div>
    @else
        <div class="text-red-700 p-3">{{ __('No areas found. Please import your area list.') }}</div>
    @endif
</div>
