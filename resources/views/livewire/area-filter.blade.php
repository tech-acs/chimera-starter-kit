<div class="py-6 sm:p-6 bg-gray-100">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="p-4">
            <div class="flex justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ __('Filter the data based on geographic area') }}
                </h3>
            </div>

            @if (! empty($dropdowns))
            <div class="md:flex md:space-x-4 lg:space-x-8 xl:space-x-10 items-baseline space-y-4 md:space-y-2" x-data="{ loading: false }">
                @foreach ($dropdowns as $levelName => $dropdown)
                    <div class="flex items-baseline space-x-4">
                        <span class="text-base leading-6 font-medium text-gray-900">{{ ucfirst(__($levelName)) }}</span>
                        <div class="relative">
                            {{--@dump($dropdown)--}}
                            @if ($dropdown['restricted'])
                                <div class="mt-1 block w-full pl-3 pr-10 py-2 bg-gray-100 text-base border border-gray-300 sm:text-sm rounded-md">
                                    {{ $dropdown['list'][$dropdown['restricted']] ?? 'restricted' }}
                                </div>
                            @else
                                <select
                                    id="{{ $levelName }}"
                                    name="{{ $levelName }}"
                                    wire:change="changeHandler('{{ $levelName }}', $event.target.value);"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option value="" @selected(empty($dropdown['selected']))>Select {{ $levelName }}</option>
                                    @foreach($dropdown['list'] as $path => $areaName)
                                        <option
                                            value="{{$path}}"
                                            @selected($path === ($dropdown['selected'] ?? null))
                                        >{{ $areaName }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="flex items-baseline space-x-4">
                    <x-button wire:click="filter" wire:loading.attr="disabled" wire:target="filter, changeHandler">
                        {{ __('Apply') }}
                    </x-button>
                    <x-danger-button wire:click="clear" wire:loading.attr="disabled" class="">
                        {{ __('Clear') }}
                    </x-danger-button>
                </div>
            </div>
            @else
                <div class="text-red-700 pt-2">{{ __('No areas found. Please import your area.') }}</div>
            @endif
        </div>
    </div>
</div>
