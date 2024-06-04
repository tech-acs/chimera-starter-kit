import Plotly from 'plotly.js-basic-dist-min';
import fr from 'plotly.js-locales/fr';
import pt from 'plotly.js-locales/pt-pt';

export default class PlotlyChart {
    id;
    data = [];
    layout = {};
    config = [];

    constructor(rootElementId) {
        this.id = rootElementId;
        console.log('2 - Constructor of PlotlyChart: ' + this.id);
        this.initializeChart();
        this.registerLivewireEventListeners();
    }

    initializeChart() {
        const el = document.getElementById(this.id)
        this.config = JSON.parse(el.dataset['config'])
        if (this.config.locale === 'fr') {
            Plotly.register(fr);
        } else if (this.config.locale === 'pt') {
            Plotly.register(pt);
        }
        Plotly.newPlot(el, this.data, this.layout, this.config);
        Livewire.dispatch(`updateRequest.${this.id}`);
        console.log('3 - Initialized Plotly and announced readiness: ' + this.id);
    }

    registerLivewireEventListeners() {
        Livewire.on(`updateResponse.${this.id}`, (dataAndLayout) => {
            console.log('4 - Received payload from backend: ' + this.id, dataAndLayout);
            Plotly.react(this.id, ...dataAndLayout)
        });
    }
}
