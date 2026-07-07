import Plotly from 'plotly.js/lib/core';

import bar from 'plotly.js/lib/bar';
import box from 'plotly.js/lib/box';
import histogram from 'plotly.js/lib/histogram';
import pie from 'plotly.js/lib/pie';
import scatter from 'plotly.js/lib/scatter';
import sunburst from 'plotly.js/lib/sunburst';

import fr from 'plotly.js-locales/fr';
import ptPT from 'plotly.js-locales/pt-pt';

Plotly.register([bar, box, histogram, pie, scatter, sunburst, fr, ptPT]);

export default Plotly;
