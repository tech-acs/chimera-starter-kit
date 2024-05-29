<div class="inline text-left">
    <a title="Test" wire:click="$set('modalOpen', true)" class="cursor-pointer text-indigo-600 hover:text-indigo-400">
        <svg class="size-5 inline" fill="currentColor" viewBox="0 0 256 256"><path d="M220,160a12,12,0,1,1-12-12A12,12,0,0,1,220,160Zm-4.55,39.29A48.08,48.08,0,0,1,168,240H144a48.05,48.05,0,0,1-48-48V151.49A64,64,0,0,1,40,88V40a8,8,0,0,1,8-8H72a8,8,0,0,1,0,16H56V88a48,48,0,0,0,48.64,48c26.11-.34,47.36-22.25,47.36-48.83V48H136a8,8,0,0,1,0-16h24a8,8,0,0,1,8,8V87.17c0,32.84-24.53,60.29-56,64.31V192a32,32,0,0,0,32,32h24a32.06,32.06,0,0,0,31.22-25,40,40,0,1,1,16.23.27ZM232,160a24,24,0,1,0-24,24A24,24,0,0,0,232,160Z"></path></svg>
    </a>

    <x-dialog-modal wire:model="modalOpen">
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

                            @if ($test['result'] == 'running')
                                <div class="inline-flex items-center gap-x-1.5 rounded-full bg-blue-100 px-1.5 py-0.5 text-xs uppercase font-medium text-blue-700">
                                    <svg class="h-4 text-blue-700" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 4m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M4 17l5 1l.75 -1.5" /><path d="M15 21l0 -4l-4 -3l1 -6" /><path d="M7 12l0 -3l5 -1l3 3l3 1" /></svg>
                                    {{ $test['result'] }}
                                </div>
                                <div class="mt-1 text-xs text-blue-500">{{ $test['result_description'] }}</div>

                            @elseif($test['result'] == 'passed')
                                <div class="inline-flex items-center gap-x-1.5 rounded-full bg-green-100 px-1.5 py-0.5 text-xs uppercase font-medium text-green-700">
                                    <svg class="h-4 text-green-700" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                    {{ $test['result'] }}
                                </div>
                                <div class="mt-1 text-xs text-green-500">{{ $test['result_description'] }}</div>

                            @elseif($test['result'] == 'failed')
                                <div class="inline-flex items-center gap-x-1.5 rounded-full bg-red-100 px-1.5 py-0.5 text-xs uppercase font-medium text-red-700">
                                    <svg class="h-4 text-red-700" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>
                                    {{ $test['result'] }}
                                </div>
                                <div class="mt-1 text-xs text-red-500">{{ $test['result_description'] }}</div>

                            @else
                                <div class="inline-flex items-center gap-x-1.5 rounded-full bg-gray-100 px-1.5 py-0.5 text-xs uppercase font-medium text-gray-700">
                                    <svg class="h-4 text-gray-500" fill="currentColor" viewBox="0 0 256 256"><path d="M184,64V192a8,8,0,0,1-16,0V64a8,8,0,0,1,16,0Zm40-8a8,8,0,0,0-8,8V192a8,8,0,0,0,16,0V64A8,8,0,0,0,224,56Zm-80,72a15.76,15.76,0,0,1-7.33,13.34L48.48,197.49A15.91,15.91,0,0,1,24,184.15V71.85A15.91,15.91,0,0,1,48.48,58.51l88.19,56.15A15.76,15.76,0,0,1,144,128Zm-16.18,0L40,72.08V183.93Z"></path></svg>
                                    {{ $test['result'] }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">{{ $test['result_description'] }}</div>
                            @endif

                        </div>
                    </li>
                @endforeach
            </ul>

        </x-slot>
        <x-slot name="footer">
            <x-action-message class="mr-3 inline-flex items-center" on="running">{{ __('Tests are running...') }}</x-action-message>
            <x-secondary-button wire:click="$toggle('modalOpen')" wire:loading.attr="disabled">{{ __('Cancel') }}</x-secondary-button>
            <x-button class="ml-4" wire:click.prevent="start()" wire:loading.attr="disabled" onclick="() => Livewire.dispatch('running');">{{ __('Start Test') }}</x-button>
        </x-slot>
    </x-dialog-modal>

</div>
