<div class="bg-white rounded-sm border-gray-200 border">
    <div class="md:flex items-baseline justify-between p-4">
        <div class="flex items-baseline">
            <select id="region" name="region" wire:change="regionSelected($event.target.value)" class="mt-1 mr-2 block w-full pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="" @if (empty($selectedRegion)) selected @endif>Allow all regions</option>
                @foreach($regions as $code => $name)
                    <option value="{{$code}}" @if ($selectedRegion === $code) selected @endif>{{$name}}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-baseline">
            <select id="district" name="district" wire:change="districtSelected($event.target.value)" class="mt-1 mr-2 block w-full pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="" @if (empty($selectedDistrict)) selected @endif>Allow all districts</option>
                @foreach($districts as $code => $name)
                    <option value="{{$code}}" @if ($selectedDistrict === $code) selected @endif>{{$name}}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-baseline">
            <select id="sa" name="sa" wire:change="saSelected($event.target.value)" class="mt-1 mr-2 block w-full pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="" @if (empty($selectedSa)) selected @endif>Allow all SAs</option>
                @foreach($sas as $code => $name)
                    <option value="{{$code}}" @if ($selectedSa === $code) selected @endif>{{$name}}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-baseline space-x-4">
            <x-jet-button wire:click.prevent="apply" wire:loading.attr="disabled">
                Apply
            </x-jet-button>
        </div>
    </div>
</div>
