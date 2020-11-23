import React from 'react';
import axios from 'axios';
import Popup from 'reactjs-popup';
import 'reactjs-popup/dist/index.css';
import GlobalSettings from "./GlobalSettings";
import AllBooks from "../AllBooks";
import {useRef} from "react/cjs/react.production.min";


const PopupExample = () => (
    <Popup open = {this.props.open} modal>
        <span> Modal content </span>
    </Popup>
);

// class RequestHandler  extends React.Component {
class RequestHandler {
    render() {
        return (
            <div>
                <button type="button" className="button" onClick={this.open.bind(this)}>
                    open
                </button>
                <Popup
                    ref={this.state.ref}
                    trigger={
                        <button type="button" className="button">
                            I am the trigger
                        </button>
                    }
                    modal
                >
                    <div>Lorem ipsum dolor sit</div>
                </Popup>
            </div>

        );
    }

    open () {
        this.state.ref.current.open();
    }

    constructor(props) {
        // super(props);
        this.ref = React.createRef();
        this.mainApiRoute = '/api/v1/';

        this.state = {
            ref: React.createRef()
        }

        this.BAD_RESULT_STATUS = 'bad result status';
    }

    async makeRequest(additionalUrlPart = '', postParams = false, specialType = false) {

        let url = GlobalSettings.mainApiRoute + additionalUrlPart;

        let loadedData = {};

        let callBackFunction = async function (requestResponse) {

            if (requestResponse.statusText !== 'OK' || requestResponse.status !== 200 || !requestResponse.hasOwnProperty('data')) {
                throw new Error(`Bad result`);
            } else {
                let responseData = requestResponse.data;
                if (responseData.hasOwnProperty('success') && responseData.success === true && responseData.hasOwnProperty('data')) {

                    if (responseData.data) {
                        return responseData.data;
                    } else {
                        return [];
                    }
                } else {

                    if (responseData.response_type === 'warning_message') {
                        let returnArray = {error: true, type: 'warning_message', message: responseData.message};
                        if (responseData.hasOwnProperty('data')) {
                            returnArray['data'] = responseData['data'];
                        }
                        return returnArray;
                    }
                }
            }


            // const persons = res.data;
            // this.setState({ persons });
        };

        if (specialType == 'delete') {
            loadedData = await axios.delete(url)
                .then(callBackFunction);
        } else {
            if (postParams) {
                loadedData = await axios.post(url, postParams)
                    .then(callBackFunction);

            } else {
                loadedData = await axios.get(url)
                    .then(callBackFunction);

            }
        }

        // throw new Error(`Bad result`);

        return loadedData;
    }
}

export default (new RequestHandler);

// export default RequestHandler;
