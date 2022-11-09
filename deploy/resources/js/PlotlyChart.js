import Plotly from 'plotly.js-basic-dist-min';
import {fr} from 'plotly.js-locales';
import pick from 'lodash/pick';

export default class PlotlyChart {
    id;
    data;
    layout;
    config;

    constructor(chartContainer, options) {
        this.collectDataPassedViaDataAttributes(chartContainer);
        this.initializeChart(chartContainer);
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
        this.id = el.id;
        this.data = this.extractDataAttributeSafely(el, 'data');
        this.layout = this.extractDataAttributeSafely(el, 'layout');
        this.config = this.extractDataAttributeSafely(el, 'config');
    }

    initializeChart(chartContainer) {
        if (this.config.locale === 'fr') {
            Plotly.register(fr);
        }
        Plotly.newPlot(chartContainer, this.data, this.layout, this.config);
    }

    registerDomEventListeners() {
        document.getElementById(this.id).on('plotly_click', function(data){
            let payload = pick(data.points[0], ['x', 'y', 'pointIndex', 'pointNumber']); // 0 here might grow per trace???
            // Also include the id in the payload so that the correct indicator can pick it up
            Livewire.emit('chartClicked', payload);
        });
    }

    registerLivewireEventListeners() {
        Livewire.on(`redrawChart-${this.id}`, (newData, newLayout) => {
            Plotly.react(this.id, newData, newLayout)
        });
    }
}
