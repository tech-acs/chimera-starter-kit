<div class="hidden sm:block">
    <div class="pb-8">
        <div wire:click.debounce="knock()" class="h-8 select-none text-right text-gray-500 align-middle">{{ $message }}</div>
        <div class="border-t border-gray-200"></div>
    </div>

    @if($developerMode)
        <x-form-section submit="updateProfileInformation">
            <x-slot name="title">
                {{ __('Developer mode') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Developer mode has been activated. It will stay active only for the current session.') }}
            </x-slot>

            <x-slot name="form">
                <div class="col-span-6 sm:col-span-4 text-lg">
                    <x-danger-button wire:click="deactivate()">Deactivate</x-danger-button>
                </div>
            </x-slot>
        </x-form-section>

        <x-section-border />
    @endif
</div>
