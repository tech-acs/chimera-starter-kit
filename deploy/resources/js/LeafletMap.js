import L from 'leaflet';

export default class LeafletMap {
    map;
    mapOptions;
    geojsonStyles;
    geojson;
    geojsonLayerGroup;

    constructor(mapContainer, options) {
        this.collectDataPassedViaDataAttributes(mapContainer);
        this.initializeMap(mapContainer, options.basemaps);
        this.initializeGeojsonLayer();
        this.registerDomEventListeners();
        this.registerLivewireEventListeners();
    }

    extractDataAttributeSafely(el, attribute) {
        try {
            return JSON.parse(el.dataset[attribute]);
        } catch (e) {
            console.log('Please set all the required data-* attributes on the element');
        }
        return undefined;
    }

    collectDataPassedViaDataAttributes(el) {
        this.mapOptions = this.extractDataAttributeSafely(el, 'mapOptions');
        this.geojsonStyles = this.extractDataAttributeSafely(el, 'styles');
    }

    initializeMap(mapContainer, basemaps) {
        this.map = L.map(mapContainer, this.mapOptions);
        let basemapLayers = {};
        let basemapsCount = basemaps.length;
        basemaps.forEach( (basemap, index) => {
            let basemapLayer = new L.tileLayer(basemap.url, basemap.options);
            if (basemapsCount === index + 1) {
                this.map.addLayer(basemapLayer);
            }
            basemapLayers[basemap.name] = basemapLayer;
        });
        L.control.layers(basemapLayers).addTo(this.map);
    }

    initializeGeojsonLayer() {
        this.geojsonLayerGroup = L.layerGroup();
        this.geojsonLayerGroup.addTo(this.map);
    }

    registerDomEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            Livewire.emit('map-ready');
        });

        this.map.addEventListener('zoomend', (e) => {
            let zoom = e.target.getZoom();
            let bounds = this.map.getBounds().toBBoxString();
            this.geojsonLayerGroup.eachLayer(function(layer) {
                console.log(layer.getLayers());
                /*if (bounds.contains(marker.getLatLng())) {
                    inBounds.push(marker.options.title);
                }*/
            });
            Livewire.emit('zoomed', zoom, bounds);
        });

        this.map.addEventListener('moveend', (e) => {
            let zoom = e.target.getZoom();
            let bounds = this.map.getBounds().toBBoxString();
            Livewire.emit('panned', zoom, bounds);
        });
    }

    registerLivewireEventListeners() {
        Livewire.on('updateMap', (geojson) => {
            console.log({'Geojson to render': geojson});
            this.renderGeojsonData(geojson);
        });
    }

    renderGeojsonData(geojson) {
        let updatedIndicatorsLayer = L.geoJSON(geojson, {
            style: (feature) => {
                return this.geojsonStyles[feature.properties.style]
            },
            onEachFeature: (feature, layer) => {
                layer.bindTooltip(feature.properties.name, {permanent: true, direction: 'center'});
                layer.on({
                    //mouseover: highlightFeature,
                    //mouseout: resetHighlight,
                    click: (e) => {
                        this.map.fitBounds(e.target.getBounds());
                        let feature = e.target.feature;
                        //console.log(feature)
                        Livewire.emit('map-clicked', feature.properties.path);
                    }
                });
            }
        });
        this.geojsonLayerGroup.addLayer(updatedIndicatorsLayer);
    };
}
