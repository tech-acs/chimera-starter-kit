@push('scripts')
    <script src="{{ mix('js/map.js') }}" defer></script>
@endpush

<div class="relative z-0">
    <div
        id="map"
        data-styles='@json($styles)'
        data-map-options='@json($mapOptions)'
        data-geojson='@json($geojson)'
        wire:ignore
        style="height: 75vh;"></div>
</div>
