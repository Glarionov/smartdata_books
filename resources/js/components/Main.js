import React from 'react';
import ReactDOM from 'react-dom';
import NotFound from './NotFound';
import AllBooks from './AllBooks';
import SingleBook from './SingleBook';
import Header from './Header';
import AuthorsForAdmin from "./AuthorsForAdmin";
import AuthorsForUsers from "./AuthorsForUsers";
import RequestHandler from "./helpers/RequestHandler";

import {
    BrowserRouter as Router,
    Switch,
    Route,
    Redirect,
} from "react-router-dom";

class Main extends React.Component {
    render() {
        return (
            <div className="main-content">
                <Router>
                    <Route render={(props) => <Header changeUser={this.changeUser.bind(this)}
                                                      {...props}
                                                      specialAccess={this.state.specialAccess}
                                                      userId={this.state.userId}
                    />}/>
                    <div className="main-body">

                        <Switch>
                            <Route path="/all_books"
                                   render={(props) => <AllBooks {...props} specialAccess={this.state.specialAccess}/>}
                            />

                            <Redirect from='/' to='/authors' exact/>

                            <Route path="/authors"
                                   render={(props) => <AuthorsForUsers {...props}
                                                                       specialAccess={this.state.specialAccess}/>}
                            />

                            <Route path="/authors_control"
                                   render={(props) => <AuthorsForAdmin {...props}
                                                                       specialAccess={this.state.specialAccess}/>}
                            />

                            <Route path="/book/:id"
                                   render={(props) => <SingleBook {...props} specialAccess={this.state.specialAccess}/>}
                            />
                            <Route component={NotFound}/>
                        </Switch>
                    </div>
                </Router>
            </div>
        )
    }

    constructor(props) {
        super(props);
        this.state = {
            userId: false,
            specialAccess: false
        }
    }

    async componentDidMount() {
        let userId = localStorage.getItem('userId');
        let authToken = localStorage.getItem('authToken');

        if (userId && authToken) {
            await RequestHandler.makeRequest('auth/user-profile', {token: authToken})
                .then(result => {
                    if (localStorage.getItem('specialAccess') && localStorage.getItem('specialAccess')) {
                        this.setState({
                            userId: localStorage.getItem('userId'),
                            specialAccess: localStorage.getItem('specialAccess')
                        });
                    }
                })
                .catch(err => {
                    localStorage.removeItem('authToken');
                    localStorage.removeItem('userId');
                    localStorage.removeItem('login');
                    localStorage.removeItem('specialAccess');
                    this.changeUser(false, false);
                });
        }
    }

    changeUser(userId, specialAccess = false) {
        this.setState({userId, specialAccess});
    }
}

if (document.getElementById('main-wrapper')) {
    ReactDOM.render(<Main/>, document.getElementById('main-wrapper'));
}
