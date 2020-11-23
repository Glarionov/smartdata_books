import React from 'react';
import RequestHandler from "./helpers/RequestHandler";
import Book from "./Book";

class AuthorsForUsers extends React.Component {

    render() {
        return (
            <div className="author-for-users">
                <div className="big-title">
                    List of All Authors
                </div>
                {Object.entries(this.state.authors).map(([index, author]) => (
                    <div className="author-info" key={index}>
                        <div className="author-name-for-users">{author.first_name} {author.last_name}</div>
                        {Object.entries(author.books).map(([bookIndex, book]) => (
                            <div className="mini-book-info">
                                {book.name}
                            </div>
                        ))}
                    </div>
                ))}
            </div>
        );
    }

    constructor(props) {
        super(props);
        this.state = {
            authors: {}
        };
    }

    async componentDidMount() {
        await this.loadAuthors();
    }

    async loadAuthors() {
        await RequestHandler.makeRequest('for-user/authors/load')
            .then(result => {
                if (result.hasOwnProperty('data')) {
                    this.setState({authors: result.data});
                }
            });
    }
}

export default AuthorsForUsers;
