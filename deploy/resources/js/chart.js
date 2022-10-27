import PlotlyChart from "./PlotlyChart";

const components = [
    {
        class: PlotlyChart,
        selector: '.chart',
        options: {}
    }
];

components.forEach(component => {
    if (document.querySelector(component.selector) !== null) {
        document.querySelectorAll(component.selector).forEach(el => new component.class(el, component.options));
    }
});
