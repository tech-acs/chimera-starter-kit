import React, {useState} from 'react';
import Modal from 'react-modal';
import Button from "./Button.jsx";
import {ErrorIcon, SuccessIcon, TemplateIcon} from "./Icons.jsx";
import {cloneDeep, isEmpty} from "lodash";

function TemplateSaver({layout, data}) {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({name: "", category: "", description: ""});
    const [formError, setFormError] = useState("");
    const [notification, setNotification] = useState({});

    const openModal = () => {
        setShowModal(true)
    }

    const closeModal = () => {
        setShowModal(false)
    }

    const handleChange = (event) => {
        const { name, value } = event.target;
        setFormData((prevFormData) => ({ ...prevFormData, [name]: value }));
    }

    const isFormValid = () => {
        if (formData.name.length < 5) {
            setFormError('Input must be at least 5 characters');
            return false;
        } else {
            setFormError('');
            return true;
        }
    }

    const save = async () => {
        const valid = isFormValid();
        if (valid) {
            const dataStrippedData = cloneDeep(data);
            dataStrippedData.forEach((trace, index) => {
                if (trace.meta.columnNames) {
                    const propertiesToRemove = Object.keys(trace.meta.columnNames);
                    propertiesToRemove.forEach(property => {
                        delete dataStrippedData[index][property];
                    });
                }
            })
            const response = await axios.post(`/manage/developer/api/chart-template`, { ...formData, data: dataStrippedData, layout }, {validateStatus: () => true});
            console.log('Response: (response.status, response.data)', response.status, response.data);
            if (response.status === 200) {
                setNotification({color: "green", icon: SuccessIcon, text: "Successfully saved"})
            } else {
                setNotification({color: "red", icon: ErrorIcon, text: "Error: " + response.data.message});
            }
            setTimeout(() => {
                setNotification({})
            },5000)
        } else {
            setNotification({color: "red", icon: ErrorIcon, text: "Please correct and resubmit form"});
        }
    };

    Modal.setAppElement('#chart-editor');

    return (
        <div className="flex">
            <Button label="Save as template" clickHandler={openModal} icon={TemplateIcon} colorClasses="bg-gray-600 hover:bg-gray-500 focus:ring-gray-500" />

            <Modal
                isOpen={showModal}
                onRequestClose={closeModal}
                contentLabel="Example Modal"
                style={
                    {
                        content: {
                            width: '60%',
                            height: '600px',
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
                    <div className="font-semibold text-xl">Chart template creator</div>
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
                    <div>
                        <div className="grid grid-cols-1 gap-6">
                            <div className="flex flex-col space-y-1">
                                <label htmlFor="name" className="block font-medium text-sm text-gray-700">Name *</label>
                                <input id="name" name="name" value={formData.name} onChange={handleChange} type="text"
                                       required={true} minLength={5}
                                       className="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"/>
                                {formError && <div className="text-xs pl-1 text-red-500">{formError}</div>}
                            </div>
                            <div className="flex flex-col space-y-1">
                                <label htmlFor="category" className="block font-medium text-sm text-gray-700">Category</label>
                                <input id="category" name="category" value={formData.category} onChange={handleChange}
                                       type="text"
                                       className="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"/>
                            </div>
                            <div className="flex flex-col space-y-1">
                                <label htmlFor="description" className="block font-medium text-sm text-gray-700">Description</label>
                                <textarea id="description" name="description" value={formData.description}
                                          onChange={handleChange}
                                          className="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"/>
                            </div>
                            <div className="flex flex-col space-y-1">
                                <label htmlFor="columns"
                                       className="block font-medium text-sm text-gray-700">Axis-data mapping</label>
                                <input id="columns" value={JSON.stringify(data[0]?.meta?.columnNames)?.replace(/['"]+/g, ' ')}
                                       type="text" onChange={handleChange}
                                       className="text-sm text-gray-500 border-gray-300 focus:outline-0 focus:ring-0 bg-gray-50 rounded-md shadow-sm"/>
                            </div>
                        </div>
                        <div className="flex justify-end gap-x-4 mt-4">
                            <div className="flex items-center text-nowrap font-medium mr-4"
                                 style={{color: notification.color}}>{notification.icon?.()} {notification?.text}</div>
                            <button type="button"
                                    onClick={closeModal}
                                    className="inline-flex items-center justify-center px-3 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 text-black shadow-sm bg-gray-300 hover:bg-gray-400">
                                Close
                            </button>
                            <button type="button"
                                    onClick={save}
                                    className="inline-flex items-center justify-center px-3 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 text-white bg-teal-600 hover:bg-teal-500 focus:ring-teal-500">
                                Save
                            </button>
                        </div>
                    </div>
                </div>

            </Modal>
        </div>
    );

}

export default TemplateSaver;
