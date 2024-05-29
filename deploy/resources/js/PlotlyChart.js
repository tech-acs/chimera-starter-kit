import Plotly from 'plotly.js-dist';

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
        Plotly.newPlot(document.getElementById(this.id), this.data, this.layout, this.config);
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
