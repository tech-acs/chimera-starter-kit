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

@push('late-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let map = L.map('map', {
                center: [7.9, -1.03],
                zoom: 6,
                attributionControl: false
            });
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

        });

    </script>
@endpush
<x-app-layout>

    <div class="w-full py-6 px-4 sm:px-6 lg:px-8">

        <x-simple-card>
            <div id="map" style="height: 700px;"></div>
        </x-simple-card>

    </div>

</x-app-layout>
