<div class="py-6 sm:p-6 bg-gray-100">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="p-4">
            <div class="flex justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Filter the data based on geographic area
                </h3>
                <div class="hidden">
                    <a href="{{route('sql.create')}}" title="Run SQL" class="cursor-pointer bg-indigo-50 block px-1 rounded-md hover:bg-indigo-200">
                        <svg class="w-6 h-6 text-indigo-700" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </a>
                </div>
            </div>

            @if (! $areas->isEmpty())
            <div class="md:flex md:space-x-4 lg:space-x-8 xl:space-x-10 items-baseline space-y-4 md:space-y-2" x-data="{ loading: false }">
                @foreach($areas as $type => $area)
                    <div class="flex items-baseline space-x-4">
                        <span class="text-base leading-6 font-medium text-gray-900">{{ ucfirst(__($type)) }}</span>
                        <div class="relative">
                            <select id="{{ $type }}" name="{{ $type }}" wire:change="changeHandler('{{ $type }}', $event.target.value);" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="" @if(empty($selections[$type])) selected @endif>Select {{ $type }}</option>
                                @foreach($area as $code => $name)
                                    <option value="{{$code}}" @if($code === ($selections[$type] ?? null)) selected @endif>{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach

                <div class="flex items-baseline space-x-4">
                    <x-jet-button wire:click="filter" wire:loading.attr="disabled" wire:target="filter, changeHandler">
                        Apply
                    </x-jet-button>
                    <x-jet-danger-button wire:click="clear" wire:loading.attr="disabled" class="">
                        Clear
                    </x-jet-danger-button>
                </div>
            </div>
            @else
                <div class="text-red-700 pt-2">No areas found for this connection. Please use the <b>import:area</b> command to import your area list.</div>
            @endif
        </div>
    </div>
</div>
