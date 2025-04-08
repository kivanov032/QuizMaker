import { Link } from "react-router-dom";
import {useRef, useState} from "react";
import {useStateContext} from "../context/ContextProvider.jsx";
import axiosClient from "../axios-client.js";

export default function Login() {

    const loginRef = useRef();
    const passwordRef = useRef();
    const {setUser, setToken} = useStateContext();
    const [errors, setErrors] = useState(null)

    const onSubmit = (ev) => {
        ev.preventDefault()
        const payload = {
            login: loginRef.current.value,
            password: passwordRef.current.value,
        }
        setErrors(null)
        axiosClient.post('/login', payload)
            .then(({ data }) => {
                setUser(data.user);
                setToken(data.token);
            })
            .catch(err => {
                const response = err.response;
                if (response && response.status === 422) {
                    if (response.data.errors) {
                        setErrors(response.data.errors);
                    } else {
                        setErrors({
                            login: [response.data.message]
                        });
                    }
                }
            });

    }

    return (
        <div className="login-container">
            <div className="login-box">
                <form onSubmit={onSubmit}>
                <h2 className="login-title">Вход</h2>
                {errors && <div className="alert">
                    {Object.keys(errors).map(key => (
                        <p key={key}>{errors[key][0]}</p>
                    ))}
                </div>
                }
                <input ref={loginRef} type="text" placeholder="Login" className="login-input" />
                <input ref={passwordRef} type="password" placeholder="Пароль" className="login-input" />
                <button className="login-button">Войти</button>
                <p className="register-text">
                    Нет аккаунта? <Link to="/signup" className="register-link">Регистрация</Link>
                </p>
                </form>
            </div>
        </div>
    );
}
