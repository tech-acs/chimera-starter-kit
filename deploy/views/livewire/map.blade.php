@once
    @push('scripts')
        @vite(['resources/css/map.css', 'resources/js/map.js'])
    @endpush
@endonce

<div class="relative z-0">
    <div
        id="map"
        data-styles='@json($styles)'
        data-map-options='@json($mapOptions)'
        data-geojson='@json($geojson)'
        wire:ignore
        style="height: 75vh;"></div>
</div>
