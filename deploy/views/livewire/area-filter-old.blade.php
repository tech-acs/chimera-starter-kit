<div class="py-6 sm:p-6 bg-gray-100">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
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

            <div class="md:flex md:space-x-4 lg:space-x-8 xl:space-x-10 items-baseline space-y-4 md:space-y-2" x-data="{ loading: false }">
                <div class="flex items-baseline space-x-4">
                    <span class="text-base leading-6 font-medium text-gray-900">Region</span>
                    <div class="relative">
                        <select id="region" name="region" wire:change="regionSelected($event.target.value);" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="" @if(empty($selectedRegion)) selected @endif @if(array_key_exists('region', $areaRestriction)) disabled @endif>Select region</option>
                            @foreach($regions as $code => $name)
                                <option value="{{$code}}" @if($code === $selectedRegion) selected @endif>{{$name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-baseline space-x-4">
                    <span class="text-base leading-6 font-medium text-gray-900">Constituency</span>
                    <div class="relative">
                        <div wire:loading wire:target="regionSelected" class="absolute inset-0 bg-white mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 sm:text-sm rounded-md">Loading...</div>
                        <select id="constituency" name="constituency" wire:change="constituencySelected($event.target.value)" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="" @if(empty($selectedConstituency)) selected @endif @if(array_key_exists('constituency', $areaRestriction)) disabled @endif>Select constituency</option>
                            @foreach($constituencies as $code => $name)
                                <option value="{{$code}}" @if($code === $selectedConstituency) selected @endif>{{$name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-baseline space-x-4">
                    <x-jet-button wire:click="filter" wire:loading.attr="disabled" wire:target="filter, regionSelected, constituencySelected">
                        Apply
                    </x-jet-button>
                    <button wire:click="clear" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-800 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-25 transition">
                        Clear
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
