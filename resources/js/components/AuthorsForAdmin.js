import React from 'react';
import RequestHandler from "./helpers/RequestHandler";
import Book from "./Book";
import InfiniteScroll from "react-infinite-scroll-component";

class AuthorsForAdmin extends React.Component {

    render() {
        return (
            <div className="authors-for-admin-wrapper">
                <div className="big-title">
                    Authors Editing Page
                </div>
                <div className="author-info author-info-new adder-block-wrapper">
                    <div className="author-name">
                        <form onSubmit={this.addNewAuthor.bind(this)}>
                            <input type="text"
                                   value={this.state.newFirstName}
                                   onChange={this.handleNewFirstNameEditing.bind(this)}
                                   placeholder="New authors' name"
                            />
                            <input type="text"
                                   className="input-with-margin"
                                   value={this.state.newLastName}
                                   onChange={this.handleNewLastNameEditing.bind(this)}
                                   placeholder="and last name"
                            />
                            <input type="submit"
                                   className="ellipse-button input-with-margin" value="Add new author"/>
                        </form>

                    </div>
                </div>

                {Object.entries(this.state.authors).map(([authorIndex, author]) => (
                    <div className="author-info" key={authorIndex}>
                        <div className="author-name">
                            <form onSubmit={this.saveAuthorChanges.bind(this, authorIndex)}>
                                <input type="text"
                                       value={author.first_name}
                                       onChange={this.handleFirstNameEdit.bind(this, authorIndex)}
                                       placeholder="First name"
                                />
                                <input type="text"
                                       className="input-with-margin"
                                       value={author.last_name}
                                       onChange={this.handleLastNameEdit.bind(this, authorIndex)}
                                       placeholder="Last name"
                                />
                                <input type="submit"
                                       className="ellipse-button input-with-margin" value="Save changes"/>
                            </form>

                        </div>
                        <div className="author-amount-of-books">
                            Wrote <b>{author.books_count}</b> books
                        </div>

                        <div className="delete-book-icon delete-icon" title="Delete author"
                             onClick={this.deleteAuthor.bind(this, authorIndex)}>
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
            newFirstName: '',
            newLastName: '',
            authors: {}
        };
    }

    async componentDidMount() {
        await RequestHandler.makeRequest('authors/load-for-admin?token=' + localStorage.getItem('authToken'))
            .then(authors => {
                this.setState({authors: authors['data']});
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

    handleNewFirstNameEditing(event) {
        this.setState({newFirstName: event.target.value});
    }

    handleNewLastNameEditing(event) {
        this.setState({newLastName: event.target.value});
    }

    async addNewAuthor(event) {
        event.preventDefault();

        let firstName = this.state.newFirstName;
        let lastName = this.state.newLastName;

        this.setState({
            newFirstName: '',
            newLastName: '',
        });

        await RequestHandler.makeRequest('authors/create/', {
            firstName,
            lastName,
            token: localStorage.getItem('authToken')
        })
            .then(response => {
                if (response.hasOwnProperty('id')) {
                    let authors = this.state.authors;
                    authors[response.id] =
                        {
                            first_name: firstName,
                            last_name: lastName
                        };
                    this.setState({authors});
                }
            }).catch(err => {
            });
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
