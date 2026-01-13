import Plotly from 'plotly.js-dist';
import fr from 'plotly.js-locales/fr';
import ptPT from 'plotly.js-locales/pt-pt';

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
        if (this.config.locale === 'fr') {
            Plotly.register(fr);
        } else if (this.config.locale === 'pt') {
            Plotly.register(ptPT);
        }
        //console.log('1 - (alpine init), 2 - PlotlyChart constructor with id: ' + this.id);
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
