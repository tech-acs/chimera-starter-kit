import React, {useState} from 'react';
import Modal from 'react-modal';
import {AgGridReact} from 'ag-grid-react';
import "ag-grid-community/styles/ag-grid.css";
import "ag-grid-community/styles/ag-theme-quartz.css";
import _ from "lodash";
import Button from "./Button.jsx";
import {TableIcon} from "./Icons.jsx";

const transformColumnSchemaToRowSchema = (data) => {
    const keys = _.keys(data);
    const arrays = _.values(data);
    const arrayOfPropertyLists = _.zip.apply(_, arrays);
    return _.map(arrayOfPropertyLists, (list) => {
        const obj = {};
        _.each(keys, function (key, i) {
            obj[key] = list[i];
        });
        return obj;
    });
}

function DataViewer({data}) {
    const [showModal, setShowModal] = useState(false);

    const openModal = () => {
        setShowModal(true)
    }

    const closeModal = () => {
        setShowModal(false)
    }

    Modal.setAppElement('#chart-editor');

    const rowData = transformColumnSchemaToRowSchema(data);
    const colDefs = _.map(_.keys(data), (col) => {
        return { field: col, cellDataType: 'text' }
    })

    return (
        <div className="flex">
            <Button label="Data" clickHandler={openModal} icon={TableIcon} colorClasses="bg-gray-600 hover:bg-gray-500 focus:ring-gray-500" />

            <Modal
                isOpen={showModal}
                onRequestClose={closeModal}
                contentLabel=""
                style={
                    {
                        content: {
                            width: '60%',
                            height: '620px',
                            margin: 'auto',
                            border: '1px solid #ffffff',
                            backgroundColor: '#f9fafb',
                            overflow: 'auto',
                            WebkitOverflowScrolling: 'touch',
                            borderRadius: '6px',
                            padding: '0px'
                        },
                        overlay: {
                            zIndex: 10,
                            background: "rgba(0, 0, 0, 0.75)"
                        }
                    }
                }
            >

                <div className="flex justify-between border-b p-4 py-3 text-gray-600 bg-gray-100">
                    <div className="font-semibold text-xl">Data Viewer</div>
                    <div>
                        <svg onClick={closeModal} className="size-6 cursor-pointer" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M18 6l-12 12"/>
                            <path d="M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
                <div className="p-10">
                    <div
                        className="ag-theme-quartz"
                        style={{height: 470}} // the grid will fill the size of the parent container
                    >
                        <AgGridReact
                            rowData={rowData}
                            columnDefs={colDefs}
                        />
                    </div>
                </div>

            </Modal>
        </div>
    );

}

export default DataViewer;
