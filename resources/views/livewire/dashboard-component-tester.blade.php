<div class="inline text-left">
    <a wire:click="$set('open', true)" class="cursor-pointer text-amber-600 hover:text-amber-900">{{ __('Test') }}</a>

    <x-dialog-modal wire:model="open">
        <x-slot name="title">
            Comprehensive Indicator Test
            <div class="text-xs text-gray-400">Hello there</div>
        </x-slot>
        <x-slot name="content">

            <ul role="list" class="divide-y divide-gray-100">
                @foreach($tests as $test)
                    <li class="flex justify-between items-start gap-x-6 gap-y-4 py-5 sm:flex-nowrap">
                        <div>
                            <div class="text-base font-semibold text-gray-900">
                                {{ $test['test'] }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ $test['test_description'] }}
                            </div>
                        </div>
                        <div class="flex flex-col items-end pt-1">
                            <div class="inline-flex items-center gap-x-1.5 rounded-full bg-red-100 px-1.5 py-0.5 text-xs uppercase font-medium text-red-700">
                                <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ $test['result'] }}
                            </div>
                            <div class="mt-1 text-xs text-red-500">
                                {{ $test['result_description'] }}
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

        </x-slot>
        <x-slot name="footer">
            <x-action-message class="mr-3 inline-flex items-center" on="processing">{{ __('Tests are running...') }}</x-action-message>
            <x-secondary-button wire:click="$toggle('open')" wire:loading.attr="disabled">{{ __('Cancel') }}</x-secondary-button>
            <x-button class="ml-4" wire:click="invite" wire:loading.attr="disabled" onclick="setTimeout(() => Livewire.emit('pleaseHideForm'), 3000);">{{ __('Start Test') }}</x-button>
        </x-slot>
    </x-dialog-modal>

</div>
