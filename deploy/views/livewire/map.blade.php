<div class="relative z-0">
    <div
        id="map"
        data-map-options='@json($mapOptions)'
        data-indicators='@json($indicators)'
        wire:ignore
        style="height: 75vh;"
    ></div>
    <div wire:loading class="absolute inset-1/2 -ml-12 -mt-6 h-12 w-48 bg-gray-500 text-white text-lg px-4 py-2 rounded-full z-[401]">Loading map data...</div>
</div>
