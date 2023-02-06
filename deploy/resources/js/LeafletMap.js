import L from 'leaflet';
import map from 'lodash/map';
import property from 'lodash/property';
import isUndefined from 'lodash/isUndefined';
import isEmpty from 'lodash/isEmpty';
import keyBy from 'lodash/keyBy';

export default class LeafletMap {
    map;
    mapOptions;
    levelZoomMapping;
    styles;
    selectedStyle;
    geojsonLayerGroup;
    options;
    startingZoom;
    movementWasZoom = false;
    indicators = {};
    locale;
    levelNames;
    levelDisplayControl;

    constructor(mapContainer, options) {
        this.options = options;
        this.collectDataPassedViaDataAttributes(mapContainer);
        this.initializeMap(mapContainer, options.basemaps);
        this.addControls();
        this.initializeGeojsonLayer(this.levelZoomMapping);
        this.registerDomEventListeners();
        this.registerLivewireEventListeners();
        // ToDo: for debugging purposes only. Remove when done!
        window.peek = this;
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
        this.indicators = this.extractDataAttributeSafely(el, 'indicators');
        this.levelZoomMapping = this.extractDataAttributeSafely(el,'levelZoomMapping');
        this.styles = this.extractDataAttributeSafely(el, 'styles');
        this.levelNames = this.extractDataAttributeSafely(el, 'levelNames');
    }

    initializeMap(mapContainer, basemaps) {
        this.locale = this.mapOptions.locale;
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

    setLegend(legendData) {
        let legend = L.DomUtil.get('legend');
        if (isEmpty(legendData)) {
            L.DomUtil.addClass(legend,'hidden');
        } else {
            L.DomUtil.removeClass(legend,'hidden');
        }
        L.DomUtil.empty(legend);
        for (const [color, label] of Object.entries(legendData)) {
            legend.innerHTML += `<i style="background-color: ${color};"></i> ${label}<br>`;
        }
    }

    addControls() {
        this.map.addControl(L.control.zoom({position: 'bottomright'}));

        this.levelDisplayControl = L.control({position: 'bottomright'});
        this.levelDisplayControl.onAdd = () => {
            const levelDisplayContainer = L.DomUtil.create('div', 'leaflet-control info');
            let span = L.DomUtil.create('span', 'font-medium text-base font-medium', levelDisplayContainer);
            span.innerText = this.levelNames[0];
            L.DomEvent.disableClickPropagation(levelDisplayContainer);
            L.DomEvent.disableScrollPropagation(levelDisplayContainer);
            return levelDisplayContainer;
        }
        this.levelDisplayControl.update = function (levelName) {
            this._container.firstChild.innerText = levelName;
        }
        this.levelDisplayControl.addTo(this.map);

        let indicatorMenu = L.control({position: 'topleft'});
        indicatorMenu.onAdd = () => {
            const menuContainer = L.DomUtil.create('div', 'leaflet-control info');
            if (isEmpty(this.indicators)) {
                L.DomUtil.addClass(menuContainer,'hidden');
            } else {
                L.DomUtil.removeClass(menuContainer,'hidden');
            }
            let index = 0;
            for (const [classPath, indicatorName] of Object.entries(this.indicators)) {
                const label = L.DomUtil.create('label', 'flex items-center px-2 py-1 cursor-pointer focus:outline-none', menuContainer);
                const input = L.DomUtil.create('input', 'h-3 w-3 text-indigo-600 border-gray-300 focus:ring-indigo-500', label);
                input.type = 'radio';
                input.name = 'indicator[]';
                input.value = classPath;
                if (index === 0) {
                    input.checked = true;
                    index++;
                }

                let span = L.DomUtil.create('span', 'ml-3 font-medium text-xs', label);
                span.innerText = indicatorName;
                input.onchange = e => {
                    let selectedIndicator = e.target.value
                    Livewire.emit('indicatorSelected', selectedIndicator, this.inferLevelFromZoom(this.map.getZoom()))
                };
            }
            L.DomEvent.disableClickPropagation(menuContainer);
            L.DomEvent.disableScrollPropagation(menuContainer);
            return menuContainer;
        }
        indicatorMenu.addTo(this.map);

        let legend = L.control({position: 'bottomleft'});
        legend.onAdd = () => {
            let legendContainer = L.DomUtil.create('div', 'info legend hidden');
            legendContainer.id = 'legend';
            return legendContainer;
        };
        legend.addTo(this.map);
    }

    highlightFeature(e) {
        const layer = e.target;
        layer.setStyle({weight: 3});
        layer.bringToFront();
    }

    resetHighlight(e) {
        const currentLevel = this.inferLevelFromZoom(this.map.getZoom());
        this.geojsonLayerGroup.getLayers()[currentLevel].setStyle({weight: 1});
    }

    initializeGeojsonLayer(levels) {
        this.geojsonLayerGroup = L.layerGroup();
        const levelsCount = levels.length;
        let emptyGeojson = {
            "type": "FeatureCollection",
            "features": []
        };
        for (let i = 0; i < levelsCount; i++) {
            let paneName = `pane${i}`;
            this.map.createPane(paneName);
            this.geojsonLayerGroup.addLayer(L.geoJSON(emptyGeojson, {
                pane: paneName,
                level: i,
                style: () => {
                    //return {weight: 1};
                    return this.styles.default;
                },
                show: () => this.map.getPane(paneName).style.display = '',
                hide: () => this.map.getPane(paneName).style.display = 'none',
                onEachFeature: (feature, layer) => {
                    layer.bindTooltip(feature.properties.name[this.locale], {permanent: false, direction: 'center'});
                    layer.on({
                        mouseover: (e) => this.highlightFeature(e),
                        mouseout: (e) => this.resetHighlight(e),
                        /*click: (e) => {
                            this.map.fitBounds(e.target.getBounds());
                            let feature = e.target.feature;
                            Livewire.emit('mapClicked', [feature.properties.path]);
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
                //console.log(feature.feature.properties.name);
                intersectingFeatures.push(feature.feature);
            }
        });
        return intersectingFeatures;
    }

    inferLevelFromZoom(zoom) {
        return this.levelZoomMapping.findIndex((zoomLevelGroup) => zoomLevelGroup.includes(zoom));
    }

    registerDomEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            Livewire.emit('mapReady', this.inferLevelFromZoom(this.map.getZoom()));
        });

        this.map.addEventListener('zoomstart', () => {
            this.movementWasZoom = true;
        });

        this.map.addEventListener('movestart', () => {
            this.startingZoom = this.map.getZoom();
        });

        this.map.addEventListener('moveend', () => {
            const previousLevel = this.inferLevelFromZoom(this.startingZoom);
            const currentLevel = this.inferLevelFromZoom(this.map.getZoom());
            //console.log({previousLevel, currentLevel})

            if ( // Do nothing if:
                (this.movementWasZoom && (previousLevel === currentLevel)) ||
                (! this.movementWasZoom && (currentLevel === 0))
            ) {
                this.movementWasZoom = false;
                return;
            }

            const levelLayers = this.geojsonLayerGroup.getLayers();
            let dictatingLevel = currentLevel - 1;
            if (previousLevel !== currentLevel) {
                dictatingLevel = previousLevel;
                levelLayers[currentLevel].options.show();
                levelLayers[previousLevel].options.hide();
                this.levelDisplayControl.update(this.levelNames[currentLevel]);
            }
            const bounds = this.map.getBounds();
            const withinBoundsFeatures = this.getFeaturesIntersectingBounds(levelLayers[dictatingLevel], bounds);
            const withinBoundsLtreePaths = map(withinBoundsFeatures, property('properties.path'));
            if (withinBoundsLtreePaths.length) {
                console.log({dictatingLevel, withinBoundsLtreePaths})
                this.movementWasZoom = false;
                Livewire.emit('mapMoved', currentLevel, currentLevel - previousLevel, withinBoundsLtreePaths);
                console.log({emitted:'mapMoved', currentLevel, direction: currentLevel-previousLevel, withinBoundsLtreePaths})
            }
        });
    }

    applyIndicatorDataToMap(level, data) {
        const currentLayer = this.geojsonLayerGroup.getLayers()[level];
        currentLayer.resetStyle();
        const areaKeyedData = keyBy(data, 'area_code');
        currentLayer.getLayers().forEach(feature => {
            let data = areaKeyedData[feature.feature.properties.code];
            //console.log({data, areaKeyedData, code: feature.feature.properties.code, data})
            if (! isUndefined(data)) {
                feature.setStyle(this.styles[this.selectedStyle][data.style]);
                feature.setTooltipContent(feature.feature.properties.name[this.locale] + ': ' + data.value);
            } else {
                feature.setTooltipContent(feature.feature.properties.name[this.locale]);
            }
        });
    }

    registerLivewireEventListeners() {
        Livewire.on('geojsonUpdated', (geojson, level, data) => {
            console.log({'Received from server': geojson, level, data});
            this.render(geojson, level);

            this.applyIndicatorDataToMap(level, data);
        });

        Livewire.on('indicatorSwitched', (data, style, legend) => {
            this.selectedStyle = style;
            this.setLegend(legend);

            const level = this.inferLevelFromZoom(this.map.getZoom());
            this.applyIndicatorDataToMap(level, data);
        });
    }

    render(geojson, level) {
        const targetLayer = this.geojsonLayerGroup.getLayers()[level];
        targetLayer.addData(geojson);
    };
}
