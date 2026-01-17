<div class="flex pr-4">
    <a title="Change indicators per page" wire:click="$set('modalOpen', true)" class="cursor-pointer text-orange-600 hover:text-orange-400">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-blue-700">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
        </svg>
    </a>

    <x-dialog-modal wire:model="modalOpen">
        <x-slot name="title">
            User's indicators per page setting
        </x-slot>
        <x-slot name="content">
            <p class="my-2">You can change the number of indicators per page here. The default value is <span class="font-semibold">{{ $defaultPageSize }}</span>.</p>
            <p class="my-2">Once you save your preference, it will be stored in your browser and will remain until you change it again or the cookie gets cleared.</p>
            <div class="py-4">
                <label class="text-base">Indicator per page </label>
                <select wire:model="pageSize" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @foreach($pageSizeOptions as $size)
                        <option value="{{ $size }}" @selected($size === ($pageSize ?? null))>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-action-message class="mr-3 inline-flex items-center" on="saved">{{ __('Saved. Refresh to see changes.') }}</x-action-message>
            <x-secondary-button wire:click="$toggle('modalOpen')" wire:loading.attr="disabled">{{ __('Close') }}</x-secondary-button>
            <x-button class="ml-4"
                wire:click.prevent="applyAndSave()"
                wire:loading.attr="disabled"
            >{{ __('Apply & Save') }}</x-button>
        </x-slot>
    </x-dialog-modal>

</div>
