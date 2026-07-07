import Plotly from './PlotlyCustomBundle.js';

export default class PlotlyChart {
    id;
    data = [];
    layout = {};
    config = [];

    constructor(rootElementId) {
        if (!rootElementId) return;

        this.id = rootElementId;
        const el = document.getElementById(this.id)
        this.config = JSON.parse(el.dataset['config'])
        this.registerLivewireEventListeners();
    }

    registerLivewireEventListeners() {
        Livewire.on(`updateResponse.${this.id}`, (dataAndLayout) => {
            //console.log('3 - Received updateResponse: ' + this.id, dataAndLayout);

            // Use requestAnimationFrame to wait for the browser to be ready to paint
            requestAnimationFrame(() => {
                const el = document.getElementById(this.id);
                if (!el) return;

                Plotly.react(this.id, ...dataAndLayout, this.config);

                // Immediately force a resize calculation while container is visible
                // This prevents the "jump" because it happens within the same paint cycle
                Plotly.Plots.resize(this.id);
            });
        });
    }
}
