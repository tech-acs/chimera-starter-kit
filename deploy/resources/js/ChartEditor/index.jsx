import ReactDOM from 'react-dom';
import ChartEditor from "./ChartEditor.jsx";
import './index.css';
import { has } from "lodash";

const rootElement = document.getElementById('chart-editor')
const indicatorId = rootElement.getAttribute('indicator')
const defaultLayout = rootElement.getAttribute('default-layout')

const response = await axios.get(`/manage/developer/api/indicator/${indicatorId}`);
console.log('Fetched initial:', response.data);
let data = response.data.data;

// ToDo: Do this for all traces and even for z... axis
if (has(data[0], 'meta.columnNames')) {
    data[0].x = response.data.dataSources[data[0].xsrc];
    data[0].y = response.data.dataSources[data[0].ysrc];
}

ReactDOM.render(<ChartEditor
    dataSources={response.data.dataSources}
    initialData={data}
    initialLayout={response.data.layout}
    config={response.data.config}
    indicatorId={indicatorId}
    indicatorTitle={response.data.title}
    defaultLayout={defaultLayout}
/>, rootElement);
