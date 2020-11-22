import React from 'react';

import InfiniteScroll from 'react-infinite-scroll-component';
import RequestHandler from "./helpers/RequestHandler";
import Book from "./Book";

class AllBooks extends React.Component {

    render() {
        return (<div className="all-books-list">
            {this.props.specialAccess &&
            <div className="book-adder-wrapper adder-block-wrapper">

                <form onSubmit={this.addNewBook.bind(this)}>
                    <input type="text" className="new-book-adder-text"
                           value={this.state.newBookName}
                           onChange={this.handleNewBookNameEditing.bind(this)}
                           placeholder="Type new book's name here and press Enter or right button"/>
                    <input type="submit" className="new-book-adder-button ellipse-button" value="Add new book"/>
                </form>
            </div>
            }

            <InfiniteScroll
                dataLength={Object.keys(this.state.books).length}
                next={this.loadMoreBooks.bind(this)}
                hasMore={this.state.hasMore}
                loader={<h4>Loading...</h4>}
                endMessage={
                    <p className="all-loaded-message">
                        <b>All books have been loaded</b>
                    </p>
                }
            >
                {Object.entries(this.state.books).map(([bookIndex, book]) => (
                    <div key={bookIndex}>
                        <Book book={book} singleBook={false} id={bookIndex} specialAccess={this.props.specialAccess}
                              history={this.props.history}/>
                    </div>
                ))}
            </InfiniteScroll>

        </div>)
    }

    constructor(props) {
        super(props);
        this.state = {
            books: {},
            lastLoadedId: 0,
            hasMore: true,
            newBookName: ''
        }
    }

    async componentDidMount() {
        await this.loadBooks(0);
    }

    async componentDidUpdate(prevProps) {
        if (prevProps.specialAccess !== this.props.specialAccess) {
        }
    }


    async loadBooks(from = 0) {
        await RequestHandler.makeRequest('books/list/' + from)
            .then(result => {
                    if (result.hasOwnProperty('data') && Object.keys(result.data).length !== 0) {
                        result = result.data;
                        /*s*/console.log('result=', result); //todo r
                        let books = this.state.books;
                        books = Object.assign(books, result);
                        let keys = Object.keys(result);
                        /*s*/console.log('keys=', keys); //todo r
                        let lastLoadedId = result[Object.keys(result).length - 1]['id'];
                        /*s*/console.log('lastLoadedId=', lastLoadedId); //todo r
                        this.setState({
                            books,
                            lastLoadedId
                        });
                    } else {
                        this.setState({
                            hasMore: false
                        });
                    }
                }
            )
            .catch(err => {
            });
    }

    async loadMoreBooks() {
        await this.loadBooks(this.state.lastLoadedId);
    }

    changePage(event) {
        event.preventDefault();
        this.props.history.push("/web/sections/");
    }

    async addNewBook(event) {
        event.preventDefault();
        console.log('Add new book')

        let newBookName = this.state.newBookName;

        await RequestHandler.makeRequest('books/create', {name: newBookName})
            .then(result => {
                    if (result.hasOwnProperty('id')) {

                        this.props.history.push("/book/" + result.id + '?editing=1');
                    } else {

                    }
                }
            )
            .catch(err => {
            });
    }

    handleNewBookNameEditing(event) {
        this.setState({newBookName: event.target.value})
    }
}

export default AllBooks;
