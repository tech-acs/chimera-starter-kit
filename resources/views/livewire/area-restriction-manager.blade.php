<div class="bg-white rounded-sm border-gray-200 border">
    @if (! $areas->isEmpty())
        <div class="p-2 pb-4 md:flex md:space-x-4 lg:space-x-8 xl:space-x-10 items-baseline space-y-4 md:space-y-2" x-data="{ loading: false }">
            @foreach($areas as $type => $area)
                <div class="flex items-baseline space-x-4">
                    <span class="text-base leading-6 font-medium text-gray-900">{{ ucfirst(__($type)) }}</span>
                    <div class="relative">
                        <select id="{{ $type }}" name="{{ $type }}" wire:change="changeHandler('{{ $type }}', $event.target.value);" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="" @if(empty($selections[$type])) selected @endif>Allow all {{str($type)->plural()}}</option>
                            @foreach($area as $code => $name)
                                <option value="{{$code}}" @if($code === ($selections[$type] ?? null)) selected @endif>{{$name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach

            <div class="flex items-baseline space-x-4">
                <x-jet-button wire:click.prevent="filter" wire:loading.attr="disabled" wire:target="filter, changeHandler">
                    {{ __('Apply') }}
                </x-jet-button>
            </div>
        </div>
    @else
        <div class="text-red-700 p-3">{{ __('No areas found. Please use the ') }} <b>import:area</b> {{ __('command to import your area list.') }}</div>
    @endif
</div>
