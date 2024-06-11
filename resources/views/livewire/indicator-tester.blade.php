<div class="inline text-left">
    <a title="Test" wire:click="$set('modalOpen', true)" class="cursor-pointer text-orange-600 hover:text-orange-400">
        Test
        {{--<svg class="size-5 inline" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 8.04l-12.122 12.124a2.857 2.857 0 1 1 -4.041 -4.04l12.122 -12.124" /><path d="M7 13h8" /><path d="M19 15l1.5 1.6a2 2 0 1 1 -3 0l1.5 -1.6z" /><path d="M15 3l6 6" /></svg>--}}
    </a>

    <x-dialog-modal wire:model="modalOpen">
        <x-slot name="title">
            Indicator Test
            <div class="text-xs text-gray-400">
                These tests try to ascertain the validity of the indicator.<br>
                Mainly, it checks that data is available and the chart is properly designed.</div>
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
            <x-secondary-button wire:click="$toggle('modalOpen')" wire:loading.attr="disabled">{{ __('Close') }}</x-secondary-button>
            <x-button class="ml-4" wire:click.prevent="start()" wire:loading.attr="disabled" onclick="() => Livewire.dispatch('running');">{{ __('Start Test') }}</x-button>
        </x-slot>
    </x-dialog-modal>

</div>
