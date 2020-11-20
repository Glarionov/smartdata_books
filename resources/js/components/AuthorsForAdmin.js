import React from 'react';
import RequestHandler from "./helpers/RequestHandler";
import Book from "./Book";
import InfiniteScroll from "react-infinite-scroll-component";

class AuthorsForAdmin extends React.Component {

    render() {
        return (
            <div className="authors-for-admin-wrapper">
                {Object.entries(this.state.authors).map(([authorIndex, author]) => (
                    <div className="author-info" key={authorIndex}>
                        <div className="author-name">
                            <form onSubmit={this.saveAuthorChanges.bind(this, authorIndex)}>
                                <input type="text"
                                       value={author.first_name}
                                       onChange={this.handleFirstNameEdit.bind(this, authorIndex)}
                                       title="First name"
                                />
                                <input type="text"
                                       className="input-with-margin"
                                       value={author.last_name}
                                       onChange={this.handleLastNameEdit.bind(this, authorIndex)}
                                       title="Last name"
                                />
                                <input type="submit"
                                       className="ellipse-button input-with-margin" value="Save changes"/>
                            </form>

                        </div>
                        <div className="author-amount-of-books">
                            Wrote <b>{author.book_amount}</b> books
                        </div>

                        <div className="delete-book-icon delete-icon" title="Delete author" onClick={this.deleteAuthor.bind(this, authorIndex)}>
                            ⨯
                        </div>
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
        await RequestHandler.makeRequest('authors/load-for-admin')
            .then(authors => {
                this.setState({authors});
            })
    }

    async deleteAuthor(index) {
        await RequestHandler.makeRequest('authors/delete/' + index + '?token=' +
            localStorage.getItem('authToken'), {}, 'delete')
            .then(response => {
                let authors = this.state.authors;
                delete authors[index];
                this.setState({authors});
            })
    }

    async saveAuthorChanges(index, event) {
        event.preventDefault();

        let authors = this.state.authors;

        let data = {
            first_name: authors[index].first_name,
            last_name: authors[index].last_name
        };

        await RequestHandler.makeRequest('authors/update/'
            , {authorId: index, data, token: localStorage.getItem('authToken')})
            .then(response => {

            })

    }

    handleFirstNameEdit(index, event) {
        let authors = this.state.authors;
        authors[index].first_name = event.target.value;
        this.setState({authors});
    }

    handleLastNameEdit(index, event) {
        let authors = this.state.authors;
        authors[index].last_name = event.target.value;
        this.setState({authors});
    }
}

export default AuthorsForAdmin;
