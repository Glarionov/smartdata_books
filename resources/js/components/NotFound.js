import React from 'react';

class NotFound extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        return  (
            <div className="not-found-page">
            <span className="not-found-big-text">
                404 : (<br/>
            </span>
            There is no content on this page.<br/>
            Probably you should visit <a href="/all_books">all books</a> page
        </div>
        );
    }
}

export default NotFound;
