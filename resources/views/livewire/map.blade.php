@push('scripts')
    @vite(['resources/css/map.css', 'resources/js/map.js'])
@endpush

<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <x-chimera-simple-card>
        <div class="relative z-0">
            <div
                id="map"
                data-map-options='@json($leafletMapOptions)'
                data-indicators='@json($indicators)'
                data-levels='@json($levels)'
                data-styles='@json($allStyles)'
                wire:ignore
                style="height: 75vh;"
            ></div>
            <div wire:loading class="absolute inset-1/2 -ml-12 -mt-6 h-12 w-48 bg-gray-500 text-white text-lg px-4 py-2 rounded-full z-[401]">Loading map data...</div>
        </div>
    </x-chimera-simple-card>
</div>
