import React from 'react';
import RequestHandler from "./helpers/RequestHandler";

class Book extends React.Component {

    render() {
        return (
            <div className="book-wrapper fading-out">

                {this.state.deleted &&
                <div className="deleted-book">
                    The book {this.state.name} have been deleted
                </div>
                }

                {!this.state.deleted &&
                <div className="not-deleted-book">
                    <div className="book-cover">
                        ?
                    </div>

                    <div className="book-text-content">
                        <div className="book-name">

                            {this.state.editing &&
                            <textarea className="book-name-editor"
                                      value={this.state.editingBookName}
                                      onChange={this.handleNameEditing.bind(this)}
                            ></textarea>
                            }

                            {!this.state.editing &&
                            <span>
                                {!this.props.singleBook &&
                                <a href={"/book/" + this.props.book.id}
                                   onClick={this.changePage.bind(this)}
                                >
                                    {/*{this.props.book.name}*/}
                                    {this.state.name}
                                </a>
                                }

                                {this.props.singleBook &&
                                <span>
                                    {this.state.name}
                                </span>

                                }
                             </span>

                            }


                        </div>

                        <div className="book-authors">
                            <i>Written by:</i><br/>
                            {Object.entries(this.state.authorsToShow).map(([authorIndex, author]) => (
                                <span key={authorIndex}>
                                {author.first_name} {author.last_name}{this.state.editing
                                && <span className="author-book-change-icon"
                                         onClick={this.deleteAuthor.bind(this, authorIndex)}>⨯</span>}
                                    <br/>
                            </span>
                            ))}

                        </div>
                    </div>


                    {
                        this.props.specialAccess && !this.state.editing &&
                        <span>
                                <div className="edit-book-icon" title="Edit book"
                                     onClick={this.startEditing.bind(this)}
                                >
                                    🖊
                                </div>

                    <div className="delete-book-icon" title="Delete book" onClick={this.deleteBook.bind(this)}>
                    ⨯
                    </div>
                    </span>

                    }

                    {
                        this.props.specialAccess && this.state.editing &&
                        <span>
                            <div className="save-editing-icon right-editor-element"
                                 onClick={this.saveEditing.bind(this)}
                            >
                                Save
                            </div>

                            <div className="cancel-editing-icon right-editor-element"
                                 onClick={this.cancelEditing.bind(this)}>
                                Cancel
                            </div>

                            <div className="new-author-adder-wrapper right-editor-element">
                                <input type="text" className="new-author-adder" placeholder="Type author's name"
                                       value={this.state.authorSearch}
                                       onChange={this.handleAuthorSearchEditing.bind(this)}
                                />

                                <div className="new-authors-list-wrapper">
                                    {!this.state.authorSearch &&
                                    <div className="author-search-info">
                                        Type author's name in form above<br/>
                                        And there will be authors with matched names, whom you could attach to book<br/>
                                        The filter triggers after you type at least 4 characters
                                    </div>
                                    }

                                    {this.state.authorSearch &&
                                    <div>
                                        {Object.entries(this.state.newFoundAuthors).map(([authorIndex, author]) => (
                                            <span key={authorIndex}>
                                                {author.first_name} {author.last_name}<span
                                                className="author-book-change-icon"
                                                title="Add author to book"
                                                onClick={this.addAuthor.bind(this, authorIndex)}>+</span>
                                                <br/>
                                         </span>
                                        ))}
                                    </div>
                                    }
                                </div>
                            </div>
                    </span>

                    }
                </div>
                }


            </div>
        )
    }

    constructor(props) {
        super(props);
        this.state = {
            editing: false,
            // editing: true,
            deleted: false,
            name: '',
            editingBookName: '',
            authorSearch: '',
            authorsToShow: {},
            authorsOnServer: {},
            newFoundAuthors: {},
            authorsToAdd: {},
            authorsToDelete: {}
        };
    }

    componentDidMount() {
        this.setState({
            name: this.props.book.name,
            editingBookName: this.props.book.name,
            authorsOnServer: {...this.props.book.authors},
            authorsToShow: {...this.props.book.authors},
            authorsToAdd: {},
            authorsToDelete: {}
        })

        if (this.props.specialAccess && this.props.singleBook) {
            this.setState({
                editing: true
            });
        }
    }

    changePage(event) {
        event.preventDefault();
        this.props.history.push("/book/" + this.props.book.id);
    }

    async deleteBook() {
        let token = localStorage.getItem('authToken');

        if (token) {
            let url = 'books/' + this.props.book.id + '?token=' + token;
            await RequestHandler.makeRequest(url, false, 'delete').then(
                result => {
                    this.setState({
                        deleted: 1
                    })
                }
            );
        }
    }

    deleteAuthor(authorIndex) {
        let authorsToShow = this.state.authorsToShow;
        delete authorsToShow[authorIndex];
        this.setState({
            authorsToShow
        });


        //authorsToAdd
        if (this.state.authorsOnServer.hasOwnProperty(authorIndex)) {
            let authorsToDelete = this.state.authorsToDelete;
            authorsToDelete[authorIndex] = true;
            this.setState({authorsToDelete});
        } else {
            if (this.state.authorsToAdd.hasOwnProperty(authorIndex)) {
                let authorsToAdd = this.state.authorsToAdd;
                delete authorsToAdd[authorIndex];
                this.setState({authorsToAdd});
            }
        }
    }

    addAuthor(authorIndex) {
        let authorsToShow = this.state.authorsToShow;
        let newFoundAuthors = this.state.newFoundAuthors;

        authorsToShow[authorIndex] = newFoundAuthors[authorIndex];
        delete newFoundAuthors[authorIndex];

        let authorsToAdd = this.state.authorsToAdd;

        authorsToAdd[authorIndex] = true;

        this.setState({
            authorsToShow,
            newFoundAuthors,
            authorsToAdd
        });
    }

    async startEditing() {
        this.setState({
            editing: true
        });
    }

    handleNameEditing(event) {
        this.setState({editingBookName: event.target.value});
    }

    async handleAuthorSearchEditing(event) {

        let authorSearch = event.target.value;
        this.setState({authorSearch: authorSearch});

        if (authorSearch.length > 3) {
            let url = 'authors/load-by-substring';

            let postData = {
                substring: authorSearch,
                authorKeys: [],
                token: localStorage.getItem('authToken')
            };

            let authorKeys = [];

            if (this.state.authorsToShow) {
                postData['authorKeys'] = Object.keys(this.state.authorsToShow);
            }

            await RequestHandler.makeRequest(url, postData).then(
                result => {
                    if (result.hasOwnProperty('data')) {
                        this.setState({
                            newFoundAuthors: result.data
                        })
                    }
                }
            );
        }
    }

    async saveEditing() {

        this.setState({
            editing: false
        });

        let editingBookName = this.state.editingBookName;
        let authorsToDelete = this.state.authorsToDelete;
        let authorsToAdd = this.state.authorsToAdd;

        let token = localStorage.getItem('authToken');

        if (token) {
            let url = 'books/update';
            await RequestHandler.makeRequest(url,
                {editingBookName, token, authorsToDelete, authorsToAdd, bookId: this.props.book.id}
            ).then(
                result => {
                    this.setState({
                        name: editingBookName,
                        authorsOnServer: this.state.authorsToShow,
                        authorsToDelete: {},
                        authorsToAdd: {}
                    })
                }
            );
        }
    }

    cancelEditing() {
        this.setState({
            editing: false,
            authorsToShow: this.state.authorsOnServer
        });
    }

    async componentDidUpdate(prevProps) {
        if (prevProps.specialAccess !== this.props.specialAccess) {
            if (!this.props.specialAccess) {
                this.setState({
                    editing: false,
                    authorsToShow: this.state.authorsOnServer
                })
            }
        }
    }
}

export default Book;
