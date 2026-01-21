<div class="relative flex flex-wrap gap-4" x-data="{ open: true }" @click.away="open = false">

    <input
        type="text"
        placeholder="Type any area name to search (at least two letter)"
        wire:model.live.debounce.300ms="query"
        @input="open = true"
        class='w-96 mt-1 mr-4 text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm'
    >
    <div class="inline-flex items-baseline space-x-4">
        <x-button wire:click="apply" wire:loading.attr="disabled" wire:target="updatedQuery">
            {{ __('Apply') }}
        </x-button>
        <x-danger-button wire:click="clear" wire:loading.attr="disabled" class="">
            {{ __('Clear') }}
        </x-danger-button>
    </div>

    <div x-cloak x-show="open && $wire.query.length >= 2" class="absolute z-[10000] translate-y-10 w-96 bg-white border mt-1 shadow-lg rounded overflow-hidden">
        @if(count($results) > 0)
            <ul>
                @foreach($results as $result)
                    <li>
                        <button
                            wire:click="selectResult('{{ $result->path }}', '{{ $result->name }}')"
                            @click="open = false"
                            class="w-full text-left px-4 py-2 hover:bg-blue-600 hover:text-white transition-colors"
                        >
                            {{ $result->displayName }}
                        </button>
                    </li>
                @endforeach
            </ul>
        @else
            <div wire:loading.remove wire:target="query" class="px-4 py-3 text-base text-gray-500 italic bg-gray-50">
                {{ __('No results found for ":query"', ['query' => $query]) }}
            </div>

            <div wire:loading wire:target="query" class="px-4 py-3 text-base text-blue-500 bg-gray-50">
                {{ __('Searching...') }}
            </div>
        @endif
    </div>
</div>
