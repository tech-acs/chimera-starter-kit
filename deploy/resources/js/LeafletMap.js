import L from 'leaflet';
import map from 'lodash/map';
import property from 'lodash/property';
import isUndefined from 'lodash/isUndefined';

export default class LeafletMap {
    map;
    mapOptions;
    styles;
    geojson;
    geojsonLayerGroup;
    options;
    level = undefined;
    data;

    constructor(mapContainer, options) {
        this.options = options;
        this.collectDataPassedViaDataAttributes(mapContainer);
        this.initializeMap(mapContainer, options.basemaps);
        this.initializeGeojsonLayer(options.levelToZoomMapping);
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
        this.styles = this.extractDataAttributeSafely(el, 'styles');
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

    initializeGeojsonLayer(levels) {
        this.geojsonLayerGroup = L.layerGroup();
        const levelsCount = levels.length;
        let emptyGeojson = {
            "type": "FeatureCollection",
            "features": []
        };
        for (let i = 0; i < levelsCount; i++) {
            this.geojsonLayerGroup.addLayer(L.geoJSON(emptyGeojson, {
                level: i,
                style: (feature) => {
                    return this.styles[feature.properties.style];
                },
                onEachFeature: (feature, layer) => {
                    layer.bindTooltip(feature.properties.name + ' - ', {permanent: false, direction: 'center'});
                    layer.on({
                        /*mouseover: () => {}, // highlightFeature,
                        mouseout: () => {}, // resetHighlight,
                        click: (e) => {
                            this.map.fitBounds(e.target.getBounds());
                            let feature = e.target.feature;
                            Livewire.emit('mapClicked', [feature.properties.path]);
                            return false;
                        }*/
                    });
                }
            }));
        }
        this.geojsonLayerGroup.addTo(this.map);
    }

    getFeaturesIntersectingBounds(layer, bounds) {
        const features = layer.getLayers();
        let intersectingFeatures = [];
        features.forEach(feature => {
            if (bounds.intersects(feature.getBounds())) {
                console.log(feature.feature.properties.name);
                intersectingFeatures.push(feature.feature);
            }
        });
        return intersectingFeatures;
    }

    inferLevelFromZoom(zoom) {
        return this.options.levelToZoomMapping.findIndex((zoomLevelGroup) => zoomLevelGroup.includes(zoom));
    }

    registerDomEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            Livewire.emit('mapReady');
        });

        this.map.addEventListener('zoomend', e => {
            let zoom = e.target.getZoom();
            let bounds = this.map.getBounds();
            Livewire.emit('mapZoomed', zoom, bounds.toBBoxString());

            const projectedLevel = this.inferLevelFromZoom(zoom);
            if ((! isUndefined(this.level)) && (projectedLevel !== this.level)) {
                if (projectedLevel > this.level) {
                    console.log({currentLevel: this.level, nextLevel: projectedLevel});
                    const levelLayers = this.geojsonLayerGroup.getLayers();
                    let withinBoundsFeatures = this.getFeaturesIntersectingBounds(levelLayers[this.level], bounds);
                    let withinBoundsPaths = map(withinBoundsFeatures, property('properties.path'));
                    Livewire.emit('levelTransitioned', withinBoundsPaths);
                    console.log({withinBoundsPaths})
                } else {
                    this.level = projectedLevel;
                }
            }
        });

        this.map.addEventListener('moveend', (e) => {
            let zoom = e.target.getZoom();
            let bounds = this.map.getBounds();
            Livewire.emit('mapPanned', zoom, bounds.toBBoxString());
        });
    }

    registerLivewireEventListeners() {
        Livewire.on('geojsonUpdated', (geojson, level, data) => {
            console.log({'Received from server': geojson, level, data});
            this.data = data;
            this.render(geojson, level);
        });
    }

    render(geojson, level) {
        let targetLayer = this.geojsonLayerGroup.getLayers()[level];
        this.level = level;
        targetLayer.addData(geojson);
        console.log({level, targetLayer: targetLayer.options.level});
    };
}
