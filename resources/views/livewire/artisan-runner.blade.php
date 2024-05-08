<div class="inline">
    <x-button wire:click.prevent="$set('modalOpen', true)">Artisan</x-button>

    <x-dialog-modal wire:model="modalOpen">
        <x-slot name="title">
            In developer mode, you are able to run some artisan commands
            <div class="text-xs text-gray-400">You can run each command separately and results will be displayed next to the close button</div>
        </x-slot>
        <x-slot name="content">

            <ul role="list" class="divide-y divide-gray-100">
                @foreach($commands as $index => $command)
                    <li class="flex justify-between items-start gap-x-6 gap-y-4 py-5 sm:flex-nowrap">
                        <div>
                            <div class="text-base font-semibold text-gray-900">
                                {{ $command['command'] }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ $command['description'] }}
                            </div>
                        </div>
                        <div class="flex flex-col items-end pt-1">
                            <x-danger-button type="button" wire:click.prevent="run({{ $index }})">Run</x-danger-button>
                        </div>
                    </li>
                @endforeach
            </ul>

        </x-slot>
        <x-slot name="footer">
            <x-chimera::better-action-message class="mr-3 inline-flex items-center" on="happening"></x-chimera::better-action-message>
            <x-secondary-button wire:click="$toggle('modalOpen')" wire:loading.attr="disabled">{{ __('Close') }}</x-secondary-button>
            {{--<x-button class="ml-4" wire:click.prevent="start()" wire:loading.attr="disabled" onclick="() => Livewire.dispatch('running');">{{ __('Start Test') }}</x-button>--}}
        </x-slot>
    </x-dialog-modal>

</div>
