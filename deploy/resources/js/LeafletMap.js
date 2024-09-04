import L from 'leaflet';
import isUndefined from 'lodash/isUndefined';
import isNull from 'lodash/isNull';
import isEmpty from 'lodash/isEmpty';
import keyBy from 'lodash/keyBy';
import { DoublyLinkedList } from './DataStructures';

export default class LeafletMap {
    map;
    mapOptions;
    styles;
    selectedStyle;
    geojsonLayerGroup;
    options;
    indicators = {};
    locale;
    levels = [];
    nav;
    levelDisplayControl;
    infoBox;

    constructor(mapContainer, options) {
        this.options = options;
        this.collectDataPassedViaDataAttributes(mapContainer);
        this.initializeMap(mapContainer, options.basemaps);
        this.addControls();
        this.initializeGeojsonLayer(this.levels);
        this.registerDomEventListeners();
        this.registerLivewireEventListeners();
    }

    extractDataAttributeSafely(el, attribute) {
        try {
            return JSON.parse(el.dataset[attribute]);
        } catch (e) {
            console.log(`Please set all the required data-* attributes on the element (${attribute} missing)`);
        }
        return undefined;
    }

    collectDataPassedViaDataAttributes(el) {
        this.mapOptions = this.extractDataAttributeSafely(el, 'mapOptions');
        this.indicators = this.extractDataAttributeSafely(el, 'indicators');
        this.levels = this.extractDataAttributeSafely(el,'levels');
        this.styles = this.extractDataAttributeSafely(el, 'styles');
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
        this.nav = new DoublyLinkedList(this.levels);
    }

    switchLayers() {
        const levelLayers = this.geojsonLayerGroup.getLayers();
        levelLayers[this.nav.position].options.show();
        if (this.nav.fitTo !== null) {
            this.map.fitBounds(this.nav.fitTo);
        }
        if (this.nav.prevPos !== this.nav.position) {
            levelLayers[this.nav.prevPos].options.hide();
        }
        this.levelDisplayControl.update(this.nav.current());
    }

    addControls() {
        //this.map.addControl(L.control.zoom({position: 'bottomright'}));
        let zoomOut = L.control({position: 'bottomright'});
        zoomOut.onAdd = () => {
            const container = L.DomUtil.create('div', 'leaflet-control info');
            let span = L.DomUtil.create('span', 'font-medium text-base cursor-pointer', container);
            span.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-zoom-out" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">\n' +
                '   <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>\n' +
                '   <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path>\n' +
                '   <path d="M7 10l6 0"></path>\n' +
                '   <path d="M21 21l-6 -6"></path>\n' +
                '</svg>';
            span.title = 'Zoom out';
            span.onclick = () => {
                if (this.nav.moveBackward()) {
                    const levelLayers = this.geojsonLayerGroup.getLayers();
                    this.nav.fitTo = levelLayers[this.nav.position].getBounds();
                    this.switchLayers();
                    this.infoBox.hide();
                }
            }
            L.DomEvent.disableClickPropagation(container);
            L.DomEvent.disableScrollPropagation(container);
            return container;
        }
        zoomOut.addTo(this.map);

        this.infoBox = L.control({position: 'bottomright'});
        this.infoBox.onAdd = () => {
            const container = L.DomUtil.create('div', 'info legend leaflet-bar hidden');
            container.id = 'info-box';
            L.DomUtil.create('span', 'font-medium text-sm', container);
            L.DomEvent.disableClickPropagation(container);
            L.DomEvent.disableScrollPropagation(container);
            return container;
        };
        this.infoBox.update = (infoHtml, ephemeral = false) => {
            let infoBox = L.DomUtil.get('info-box');
            infoBox.innerHTML = infoHtml;
            L.DomUtil.removeClass(infoBox,'hidden');
            if (ephemeral) {
                setTimeout(() => {
                    L.DomUtil.addClass(infoBox,'hidden');
                }, 5000);
            }
        };
        this.infoBox.hide = () => {
            let infoBox = L.DomUtil.get('info-box');
            L.DomUtil.addClass(infoBox,'hidden');
        };
        this.infoBox.addTo(this.map);

        this.levelDisplayControl = L.control({position: 'topright'});
        this.levelDisplayControl.onAdd = () => {
            const levelDisplayContainer = L.DomUtil.create('div', 'leaflet-control info');
            let span = L.DomUtil.create('span', 'font-medium text-base font-medium', levelDisplayContainer);
            span.innerText = this.nav.current();
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
                    Livewire.dispatch('indicatorSelected', {mapIndicator: selectedIndicator})
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

    highlightFeature(e) {
        const layer = e.target;
        layer.setStyle({weight: 3});
        layer.bringToFront();
    }

    resetHighlight(e) {
        this.geojsonLayerGroup.getLayers()[this.nav.position].setStyle({weight: 1});
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
                    return this.styles.default;
                },
                show: () => this.map.getPane(paneName).style.display = '',
                hide: () => this.map.getPane(paneName).style.display = 'none',
                onEachFeature: (feature, layer) => {
                    layer.bindTooltip(feature.properties.name[this.locale], {permanent: false, direction: 'center'});
                    layer.on({
                        mouseover: (e) => this.highlightFeature(e),
                        mouseout: (e) => this.resetHighlight(e),
                        click: (e) => {
                            this.nav.fitTo = e.target.getBounds();
                            //console.log(this.nav, this.nav.canMoveForward())
                            let feature = e.target.feature;
                            if (this.nav.canMoveForward()) {
                                Livewire.dispatch('mapClicked', {path: feature.properties.path});
                            }
                            if ((feature.properties.info !== undefined) && (feature.properties.info !== null)) {
                                this.infoBox.update(feature.properties.info);
                            } else {
                                this.infoBox.hide();
                            }
                            //console.log({trigger: 'map click', action: 'about to emit (mapClicked) to livewire (updateMap method) path:' + feature.properties.path})
                        }
                    });
                }
            }));
        }
        this.geojsonLayerGroup.addTo(this.map);
    }

    registerDomEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            Livewire.dispatch('mapReady');
        });
    }

    registerLivewireEventListeners() {
        Livewire.on('indicatorSwitched', ({style, legend}) => {
            console.log({style, legend})
            this.selectedStyle = style;
            this.setLegend(legend);

            const levelLayers = this.geojsonLayerGroup.getLayers();
            const bounds = levelLayers[0].getBounds();
            if (bounds.isValid()) {
                this.nav.reset();
                this.nav.fitTo = bounds;
                this.switchLayers();
                this.infoBox.hide();
            }
        });

        Livewire.on('backendResponse', ({geojson, level, data}) => {
            console.log({geojson, level, data})
            if (geojson !== null) {
                if (level > this.nav.position) {
                    this.nav.moveForward();
                }
                this.render(geojson, level);
                this.switchLayers();
                this.applyIndicatorDataToMap(level, data);
            } else {
                console.log('No sub-maps found');
            }
        });
    }

    applyIndicatorDataToMap(level, data) {
        const currentLayer = this.geojsonLayerGroup.getLayers()[level];
        //currentLayer.resetStyle();
        const areaKeyedData = keyBy(data, 'area_code');
        currentLayer.getLayers().forEach(feature => {
            let data = areaKeyedData[feature.feature.properties.code];
            //console.log({data, areaKeyedData, code: feature.feature.properties.code, data})
            if (! isUndefined(data)) {
                feature.setStyle(this.styles[this.selectedStyle][data.style]);
                const displayValue = isNull(data.display_value) ? data.value : data.display_value;
                feature.setTooltipContent(feature.feature.properties.name[this.locale] + ': ' + displayValue);
                feature.feature.properties.info = data.info;
            }
        });
    }

    render(geojson, level) {
        const targetLayer = this.geojsonLayerGroup.getLayers()[level];
        targetLayer.addData(geojson);
        //console.log({targetLayer:level, currentLevel:this.nav.current(), geojson, count:Object.keys(targetLayer._layers).length})
    };
}
