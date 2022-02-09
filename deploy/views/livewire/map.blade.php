@once
    @push('scripts')
        <script src="{{ mix('js/map.js') }}" defer></script>
        <script>
            const styles = {
                blank: {
                    weight: 1,
                    opacity: 1,
                    color: '#494747',
                    fillOpacity: 0.3,
                    fillColor: '#0942ee'
                },
                green: {
                    weight: 2,
                    opacity: 1,
                    color: '#494747',
                    fillOpacity: 0.3,
                    fillColor: '#60ef06'
                }
            }
            const basemaps = [
                {
                    "name":"Google Hybrid",
                    "url":"http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}",
                    "options":{
                        "minZoom": 6,
                        "maxZoom": 20,
                        "subdomains": ["mt0","mt1","mt2","mt3"]
                    },
                },
                {
                    "name":"Open Topo Map",
                    "url":"https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png",
                    "options":{
                        "maxZoom": 17
                    },
                },
                {
                    "name":"Blank Background",
                    "url":"",
                    "options":"",
                },
                {
                    "name":"Open Street Map",
                    "url":"https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                    "options":{
                        "maxZoom": 19
                    },
                },
            ]
            var maps = {};
        </script>
    @endpush
@endonce

@push('late-scripts')
    <script>
        function attachDataToArea(feature, layer) {
            let tooltip = '';
            if (feature.properties && feature.properties.code) {
                tooltip = feature.properties.name + ': ' + (data[feature.properties.code] || 'No data available');
            }
            layer.bindTooltip(tooltip); //{permanent: true, direction:"center"}
        }

        function styleArea(feature) {
            if (data[feature.properties.code] && data[feature.properties.code] > 0) {
                return styles.green;
            }
            return styles.blank;
        }

        function renderGeoJson(map, geoJ) {
            try {
                geoJsonLayer = L.geoJSON(geoJ, {
                    style: styleArea,
                    onEachFeature: attachDataToArea,
                }).addTo(map);
                map.panTo(geoJsonLayer.getBounds().getCenter());
                map.fitBounds(geoJsonLayer.getBounds());
            } catch (e) {
                console.log(e.toString())
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            let map = L.map('{!! $graphDiv !!}', {
                center: [7.9, -1.03],
                zoom: 6,
                attributionControl: false
            });
            maps['{!! $graphDiv !!}'] = map;
            let baseLayers = {};
            let totalBasemaps = basemaps.length;
            basemaps.forEach(function (basemap, index) {
                let basemapLayer = new L.tileLayer(basemap.url, basemap.options);
                if (totalBasemaps === index + 1) {
                    map.addLayer(basemapLayer);
                }
                baseLayers[basemap.name] = basemapLayer;
            });
            L.control.layers(baseLayers).addTo(map);

            data = {!! $data !!};
            renderGeoJson(map, {!! $layout !!});
        }, false);

        Livewire.on("redrawChart-{!! $graphDiv !!}", (d, layout) => {
            data = JSON.parse(d)
            let geoJ = JSON.parse(layout)
            let map = maps['{!! $graphDiv !!}']
            map.eachLayer(function (layer) {
                if (!(layer instanceof L.TileLayer)) {
                    map.removeLayer(layer);
                }
            });
            renderGeoJson(map, geoJ);
        });
    </script>
@endpush

<div class="relative z-0">
    <div id="{{$graphDiv}}" wire:ignore style="width: 100%; height: @if($mode === 'Full Page') 800px; @else 490px; @endif"></div>
    <div wire:loading.flex class="absolute inset-0 justify-center items-center opacity-80 bg-white" style="z-index: 10000;">
        Updating...
        <svg class="animate-spin h-5 w-5 mr-3 ..." viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="gray" stroke-width="4"></circle>
            <path class="opacity-75"  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    <div
            style="z-index: 9999;"
        x-show="show_help"
        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="transition duration-1000 ease-in-out absolute inset-0 justify-center items-center opacity-90 bg-white px-4 py-5 sm:px-6"
        x-cloak
    >
        {!! $help !!}
    </div>
</div>
