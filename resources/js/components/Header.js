import React from 'react';

import RequestHandler from "./helpers/RequestHandler";

class Header extends React.Component {
    render() {
        return (
            <header className="header-wrapper">
                <div className="main-header-links">

                    {this.state.specialAccess &&
                    <div className="all-books-link">
                        <a href={"/all_books/"}
                           onClick={this.changePage.bind(this, '/all_books/')}
                        >
                            All Books
                        </a>
                    </div>
                    }

                    {this.state.specialAccess &&
                    <div className="all-authors-link">
                        <a href={"/authors_control/"}
                           onClick={this.changePage.bind(this, '/authors_control/')}
                        >
                            Authors (control page)
                        </a>
                    </div>
                    }

                    <div className="all-authors-link">

                        <a href={"/authors/"}
                           onClick={this.changePage.bind(this, '/authors/')}
                        >
                            Authors
                        </a>
                    </div>

                </div>

                <div className="auth-part">

                    {this.state.isLogged &&
                    <div className="loggined">
                        <div className="header-username">
                            {this.state.login}
                        </div>
                        <div className="custom-button" onClick={this.logOut.bind(this)}>
                            Log out
                        </div>
                    </div>
                    }
                    {!this.state.isLogged &&

                    <div className="not-loggined">
                        <div className="auth-messages">
                            {this.state.authMessage}
                        </div>
                        {this.state.signingIn &&
                        <div className="sign-in-forms">
                            <form onSubmit={this.login.bind(this)}>


                            <div className="login-password-forms">
                                <input type="text" placeholder="Login" value={this.state.signInLogin} onChange={this.handleChangeSignInLogin.bind(this)}  />
                                <input type="password" placeholder="Password" value={this.state.signInPassword} onChange={this.handleChangeSignInPassword.bind(this)} />
                            </div>

                            <div className="login-buttons">
                                <input type="submit" className="custom-button" value="Log in" />
                                <div className="sign-in-plink header-cancel-button custom-button" onClick={this.openSignIn.bind(this, false)}>
                                    Cancel
                                </div>
                            </div>
                            </form>
                        </div>
                        }

                        {this.state.signingUp &&
                        <div className="sign-up-forms">
                            <form onSubmit={this.register.bind(this)}>
                            <div className="login-password-forms">
                                <input type="text" placeholder="Login" value={this.state.signUpLogin}
                                       onChange={this.handleChangeSignUpLogin.bind(this)}/>
                                <input type="password" placeholder="Password" value={this.state.signUpPassword}
                                       onChange={this.handleChangeSignUpPassword.bind(this)}/>
                                <input type="password" placeholder="Confirm Password" value={this.state.signUpPasswordConfirm}
                                       onChange={this.handleChangeSignUpPasswordConfirm.bind(this)}/>
                            </div>

                            <div className="login-buttons">
                                <input type="submit" className="custom-button" value="Register" />
                                <div className="sign-in-plink header-cancel-button custom-button"
                                     onClick={this.openSignUp.bind(this, false)}>
                                    Cancel
                                </div>
                            </div>
                            </form>
                        </div>
                        }

                        {!this.state.signingIn && !this.state.signingUp &&
                        <div>
                            <div className="sign-in-plink custom-button" onClick={this.openSignIn.bind(this)}>
                                Sign in
                            </div>
                            <div className="sign-up-plink custom-button" onClick={this.openSignUp.bind(this)}>
                                Sign up
                            </div>
                        </div>
                        }
                    </div>
                    }
                </div>
            </header>
        );
    }

    constructor(props) {
        super(props);
        this.state = {
            isLogged: false,
            login: '',
            signingIn: false,
            signingUp: false,
            signInLogin: '',
            signInPassword: '',
            signUpLogin: '',
            signUpPassword: '',
            signUpPasswordConfirm: '',
            authMessage: '',
            specialAccess: false
        }

        if (localStorage.getItem('login')) {
            this.state.isLogged = true;
            this.state.login = localStorage.getItem('login');
        }
    }

    componentDidMount() {
        this.setState({
            specialAccess: this.props.specialAccess
        });
    }

    changePage(linkPart, event) {
        event.preventDefault();
        this.props.history.push(linkPart);
    }

    openSignIn(value = true) {
        this.setState({
            signingIn: value,
            authMessage: '',
        });
    }

    openSignUp(value = true) {
        this.setState({
            signingUp: value,
            authMessage: '',
        });
    }

    handleChangeSignInLogin(event) {
        this.setState({signInLogin: event.target.value});
    }

    handleChangeSignInPassword(event) {
        this.setState({signInPassword: event.target.value});
    }

    handleChangeSignUpLogin(event) {
        this.setState({signUpLogin: event.target.value});
    }

    handleChangeSignUpPassword(event) {
        this.setState({signUpPassword: event.target.value});
    }

    handleChangeSignUpPasswordConfirm(event) {
        this.setState({signUpPasswordConfirm: event.target.value});
    }

    async login(event) {
        event.preventDefault();

        let login = this.state.signInLogin;
        let password = this.state.signInPassword;

        this.setState({
            authMessage: 'Trying to log in...'
        });

        await RequestHandler.makeRequest('auth/login', {login, password})
            .then(result => {
                if (result.error) {
                    switch (result.type) {
                        case 'warning_message':
                                if (result.message === 'no user by login') {
                                    this.setState({
                                        authMessage: 'Authorization fail'
                                    });
                                }
                            break;
                    }
                } else {
                    this.handleLoginSuccess(result);
                }
            });
    }

    async register(event) {
        event.preventDefault();
        let login = this.state.signUpLogin;
        let password = this.state.signUpPassword;
        let passwordConfirm = this.state.signUpPasswordConfirm;

        if (password !== passwordConfirm) {
            this.setState({
                authMessage: 'Passwords don\'t match!'
            });
            return false;
        }

        if (password.length < 5) {
            this.setState({
                authMessage: 'The password must be at least 5 characters'
            });
            return false;
        }

        this.setState({
            authMessage: 'Trying to create account...'
        });

        await RequestHandler.makeRequest('auth/register',
            {login, password, password_confirmation: passwordConfirm})
            .then(result => {
                if (result.error) {
                    switch (result.type) {
                        case 'warning_message':
                                this.setState({
                                    authMessage: 'Authorization fail'
                                });

                                if (result.hasOwnProperty('data')) {
                                    let data = result.data;
                                    if (data.hasOwnProperty('password')) {
                                        this.setState({
                                            authMessage: data.password
                                        });
                                    }
                                }
                            break;
                    }
                } else {
                    this.handleLoginSuccess(result, result.special_access);
                }
            });
    }

    handleLoginSuccess(userData, specialAccess = false) {
        let token = userData.data.access_token;
        let userId = userData.data.user.id;
        let login = userData.data.user.login;

        localStorage.setItem('authToken', token);
        localStorage.setItem('userId', userId);
        localStorage.setItem('login', login);
        localStorage.setItem('specialAccess', userData.data.specialAccess);

        if (userData.data.specialAccess) {
            this.setState({specialAccess: true});
        }

        this.props.changeUser(userId, specialAccess);

        this.setState({
            isLogged: true,
            authMessage: '',
            login
        });
    }

    async logOut() {

        let token = localStorage.getItem('authToken');
        this.props.changeUser(0, false);

        this.setState({
            isLogged: false,
            login: ''
        });

        await RequestHandler.makeRequest('auth/logout',
            {token}
            )
            .then(result => {
                localStorage.removeItem('authToken');
                localStorage.removeItem('userId');
                localStorage.removeItem('login');
                localStorage.removeItem('specialAccess');
                }
            )
            .catch(err => {
                this.setState({
                    authMessage: 'Could not erase logout token'
                });
            });
    }

    async componentDidUpdate(prevProps) {
        if (prevProps.userId !== this.props.userId) {
            if (!this.props.userId && this.state.isLogged) {
                await this.logOut();
            }
        }

        if (prevProps.specialAccess !== this.props.specialAccess) {
            if (this.props.specialAccess) {
                this.setState({specialAccess: true});
            }
        }
    }

}

export default Header;
