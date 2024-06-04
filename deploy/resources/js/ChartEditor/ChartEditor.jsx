import React, { useState } from 'react';
import plotly from 'plotly.js-dist';
import PlotlyEditor from 'react-chart-editor';
import 'react-chart-editor/lib/react-chart-editor.css';
import DataViewer from "./DataViewer.jsx";
import TemplateSaver from "./TemplateSaver.jsx";
import Button from "./Button.jsx";
import {ErrorIcon, ResetIcon, SaveIcon, SuccessIcon, TemplateIcon} from "./Icons.jsx";
import fr from 'plotly.js-locales/fr';
import pt from 'plotly.js-locales/pt-pt';

function ChartEditor({dataSources, initialData, initialLayout, config, indicatorId, indicatorTitle, defaultLayout}) {
    const [data, setData] = useState(initialData);
    const [layout, setLayout] = useState(initialLayout);
    const [frames, setFrames] = useState([]);
    const [notification, setNotification] = useState({});

    const updateState = (data, layout, frames) => {
        setData(data);
        setLayout(layout);
        setFrames(frames);
    };

    const save = async () => {
        // ToDo: Remove x and y from data before sending
        const response = await axios.post(`/manage/developer/api/indicator/${indicatorId}`, { data, layout }, {validateStatus: () => true});
        console.log('Saving: (traces, layout, response.status, response.data)', data, layout, response.status, response.data);
        if (response.status === 200) {
            setNotification({color: "green", icon: SuccessIcon, text: "Successfully saved"})
        } else {
            setNotification({color: "red", icon: ErrorIcon, text: "Error: " + response.data.message});
        }
        setTimeout(() => {
            setNotification({})
        },5000)
    };

    const reset = () => {
        setLayout(JSON.parse(defaultLayout));
    }

    if (config.locale === 'fr') {
        plotly.register(fr);
    } else if (config.locale === 'pt') {
        plotly.register(pt);
    }

    /*const traceTypesConfig = {
        traces: (_) => [
            {
                value: 'scatter',
                icon: 'scatter',
                label: _('Scatter'),
            },
            {
                value: 'line',
                label: _('Line'),
            },
            {
                value: 'area',
                label: _('Area'),
            },
            {
                value: 'bar',
                label: _('Bar'),
            },
            {
                value: 'histogram',
                label: _('Histogram'),
            },
            {
                value: 'table',
                label: _('Table'),
            },
            {
                value: 'pie',
                label: _('Pie'),
            },
            {
                value: 'box',
                label: _('Box'),
            },
            {
                value: 'histogram2d',
                label: _('Histogram 2D'),
            },
        ],
        complex: true,
    };*/

    return (
        <>
            <div className="flex justify-between w-full bg-gray-50">
                <div className="text-gray-600 text-xl font-medium items-center p-3 w-1/4 xl:w-1/3 2xl:w-1/2 truncate">{indicatorTitle}</div>
                <div className="flex justify-end gap-x-4 p-3">
                    <div className="flex items-center text-nowrap font-medium mr-8"
                         style={{color: notification.color}}>{notification.icon?.()} {notification?.text}</div>
                    <Button label="Reset to defaults" clickHandler={reset} icon={ResetIcon}
                            colorClasses="bg-red-600 hover:bg-red-500 focus:ring-red-500"/>
                    <DataViewer data={dataSources}/>
                    <TemplateSaver layout={layout} data={data} />
                    {/*<Button label="Save as template" clickHandler={() => {}} icon={TemplateIcon} />*/}
                    <Button label="Save" clickHandler={save} icon={SaveIcon}
                            colorClasses="bg-teal-600 hover:bg-teal-500 focus:ring-teal-500"/>
                </div>
            </div>

            <PlotlyEditor
                data={data}
                layout={layout}
                config={config}
                frames={frames}
                dataSources={dataSources}
                dataSourceOptions={Object.keys(dataSources).map((name) => ({
                    value: name,
                    label: name,
                }))}
                plotly={plotly}
                onUpdate={updateState}
                locale={config.locale}
                dictionaries={{ fr: { "Vertical": "Verticale", "Type": "Le genre" } }}
                useResizeHandler
                debug={true}
                advancedTraceTypeSelector={true}
                //traceTypesConfig={traceTypesConfig}
            />
        </>

    );
}

export default ChartEditor;
