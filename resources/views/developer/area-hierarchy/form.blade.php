<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="w-1/2">
            <x-label for="name" value="{{ __('Name') }} *" />
            <x-chimera::multi-lang-input name="name" type="text" value="{{ old('name', $areaHierarchy->name ?? null) }}" autofocus />
            <x-input-error for="name" class="mt-2" />
        </div>
        <div>
            <x-label for="zero_pad_length" value="{{ __('Zero pad code to length') }} *" />
            <x-input name="zero_pad_length" class="mt-1" type="number" min="0" value="{{ old('zero_pad_length', $areaHierarchy->zero_pad_length ?? 0) }}" />
            <small>(Set 0 for no zero-padding)</small>
            <x-input-error for="zero_pad_length" class="mt-2" />
        </div>
        <div>
            <x-label for="simplification_tolerance" value="{{ __('Shape simplification tolerance') }} *" />
            <x-input name="simplification_tolerance" class="mt-1" type="number" step="any" min="0" value="{{ old('simplification_tolerance', $areaHierarchy->simplification_tolerance ?? 0) }}" />
            <small>(Set 0 for no simplification)</small>
            <x-input-error for="simplification_tolerance" class="mt-2" />
        </div>
        {{--<div>
            <x-label for="map_zoom_levels" value="{{ __('Corrosponding map zoom levels') }} *" />
            <div>
                <x-input name="zoom_start" min="6" max="21" type="number" step="1" class="mt-1 mr-2 w-24" value="{{ old('zoom-start', $areaHierarchy->zoom_start ?? null) }}" /> &#8211;
                <x-input name="zoom_end" min="6" max="21" type="number" step="1" class="w-24 ml-2" value="{{ old('zoom-end', $areaHierarchy->zoom_end ?? null) }}" />
                <x-input-error for="map_zoom_levels" class="mt-2" />
            </div>
        </div>--}}
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-secondary-button class="mr-2"><a href="{{ route('developer.area-hierarchy.index') }}">{{ __('Cancel') }}</a></x-secondary-button>
        <x-button>
            {{ __('Submit') }}
        </x-button>
    </div>
</div>
