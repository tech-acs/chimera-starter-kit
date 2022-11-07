import LeafletMap from './LeafletMap'

const components = [
    {
        class: LeafletMap,
        selector: '#map',
        options: {
            levelToZoomMapping: [
                [5, 6, 7, 8],
                [9, 10, 11, 12, 13, 14],
                [15, 16, 17, 18, 19, 20, 21, 22]
            ],
            basemaps: [
                {
                    "name":"Google Hybrid",
                    "url":"http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}",
                    "options":{
                        "minZoom": 5,
                        "maxZoom": 20,
                        "subdomains": ["mt0","mt1","mt2","mt3"]
                    },
                },
                {
                    "name":"Open Topo Map",
                    "url":"https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png",
                    "options":{
                        "minZoom": 5,
                        "maxZoom": 17
                    },
                },
                {
                    "name":"Blank Background",
                    "url":"",
                    "options":{
                        "minZoom": 5,
                        "maxZoom": 20
                    },
                },
                {
                    "name":"Open Street Map",
                    "url":"https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                    "options":{
                        "minZoom": 5,
                        "maxZoom": 19
                    },
                },
            ],
        }
    }
];

components.forEach(component => {
    if (document.querySelector(component.selector) !== null) {
        document.querySelectorAll(component.selector).forEach(el => new component.class(el, component.options));
    }
});
