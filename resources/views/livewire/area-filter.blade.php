<div class="py-6 sm:p-6 bg-gray-100">
    <div class="bg-white shadow sm:rounded-lg" x-data="{mode: $wire.entangle('mode')}">
        <div class="p-4">
            <div class="flex justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ __('Filter the data based on geographic area') }}
                </h3>
                <div class="flex items-center gap-2">
                    <div class="hidden sm:block text-gray-400 text-sm">Switch mode</div>
                    <button title="{{ __('Drilldown <-> Search') }}" wire:click="switchMode" type="button" class="rounded-full inline-flex items-center gap-2 bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/10 dark:text-white dark:shadow-none dark:ring-white/5 dark:hover:bg-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3 text-gray-500">
                            <path fill-rule="evenodd" d="M10.47 2.22a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06l-2.25 2.25a.75.75 0 1 1-1.06-1.06l.97-.97H5.75a.75.75 0 0 1 0-1.5h5.69l-.97-.97a.75.75 0 0 1 0-1.06Zm-4.94 6a.75.75 0 0 1 0 1.06l-.97.97h5.69a.75.75 0 0 1 0 1.5H4.56l.97.97a.75.75 0 1 1-1.06 1.06l-2.25-2.25a.75.75 0 0 1 0-1.06l2.25-2.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <div x-cloak x-show="mode === 'search'" class="col-start-1 row-start-1 h-fit">
                    <livewire:live-search :removeLastNLevels="$removeLastNLevels" />
                </div>

                <div x-show="mode === 'select'" class="col-start-1 row-start-1 h-fit">
                    @if (! empty($dropdowns))
                        <div class="flex flex-wrap items-baseline gap-y-4 gap-x-6" x-data="{ loading: false }">
                            @foreach ($dropdowns as $levelName => $dropdown)
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-base leading-6 font-medium text-gray-900">{{ ucfirst(__($levelName)) }}</span>
                                    <div class="relative">
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
                                                        @selected($path == ($dropdown['selected'] ?? null))
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
    </div>
</div>
