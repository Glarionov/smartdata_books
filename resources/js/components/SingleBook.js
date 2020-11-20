import React from 'react';
import Book from "./Book";
import RequestHandler from "./helpers/RequestHandler";

class SingleBook extends React.Component {

    render() {
        return (
            <div className="single-book-wrapper">
                {this.state.noBookById &&
                <span classsName="no-book-message">
                        Could not load this book
                    </span>
                }
                {this.state.book &&
                <div>
                    <Book book={this.state.book} specialAccess={this.props.specialAccess} singleBook={true}/>
                </div>
                }
            </div>
        );
    }

    constructor(props) {
        super(props);
        this.state = {
            book: false,
            noBookById: false,
            str: ''
        };
    }

    async componentDidMount() {
        await this.loadBook(0);
    }

    async loadBook(from = 0) {

        const id = this.props.match.params.id;

        await RequestHandler.makeRequest('books/' + id)
            .then(result => {
                    if (Object.keys(result).length !== 0) {

                        this.setState({
                            book: result
                        });

                    } else {
                        this.setState({
                            noBookById: true
                        });
                    }
                }
            )
            .catch(err => {
            });
    }
}

export default SingleBook;
