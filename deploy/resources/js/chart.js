import Plotly from 'plotly.js-dist-min'


Plotly.newPlot('households.birth-rate');

Livewire.on("redrawChart-{!! $graphDiv !!}", (data, layout) => {
    let newData = JSON.parse(data)
    let newLayout = JSON.parse(layout)
    Plotly.react("{!! $graphDiv !!}", newData, newLayout)
});

/*/!*document.getElementById('{!! $graphDiv !!}').on('plotly_click', function(data){
    let payload = _.pick(data.points[0], ['x', 'y', 'pointIndex', 'pointNumber']); // 0 here might grow per trace???
    Livewire.emit('chartClicked', payload);
});*!/*/
