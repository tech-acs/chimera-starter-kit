<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="w-1/2">
            <x-jet-label for="name" value="{{ __('Name') }} *" />
            <x-chimera::multi-lang-input name="name" type="text" value="{{ old('name', $areaHierarchy->name ?? null) }}" autofocus />
            <x-jet-input-error for="name" class="mt-2" />
        </div>
        <div>
            <x-jet-label for="zero_pad_length" value="{{ __('Zero pad code to length') }} *" />
            <x-jet-input name="zero_pad_length" class="mt-1" type="number" min="0" value="{{ old('zero_pad_length', $areaHierarchy->zero_pad_length ?? 0) }}" />
            <small>(Set 0 for no zero-padding)</small>
            <x-jet-input-error for="zero_pad_length" class="mt-2" />
        </div>
        <div>
            <x-jet-label for="simplification_tolerance" value="{{ __('Shape simplification tolerance') }} *" />
            <x-jet-input name="simplification_tolerance" class="mt-1" type="number" step="any" min="0" value="{{ old('simplification_tolerance', $areaHierarchy->simplification_tolerance ?? 0) }}" />
            <small>(Set 0 for no simplification)</small>
            <x-jet-input-error for="simplification_tolerance" class="mt-2" />
        </div>
        <div>
            <x-jet-label for="map_zoom_levels" value="{{ __('Corrosponding map zoom levels') }} *" />
            <div>
                <x-jet-input name="zoom_start" min="6" max="21" type="number" step="1" class="mt-1 mr-2 w-24" value="{{ old('zoom-start', $areaHierarchy->zoom_start ?? null) }}" /> &#8211;
                <x-jet-input name="zoom_end" min="6" max="21" type="number" step="1" class="w-24 ml-2" value="{{ old('zoom-end', $areaHierarchy->zoom_end ?? null) }}" />
                <x-jet-input-error for="map_zoom_levels" class="mt-2" />
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-secondary-button class="mr-2"><a href="{{ route('developer.area-hierarchy.index') }}">{{ __('Cancel') }}</a></x-jet-secondary-button>
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
