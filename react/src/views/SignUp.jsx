import { Link } from "react-router-dom";
import {useRef, useState} from "react";
import axiosClient from "../axios-client.js";
import {useStateContext} from "../context/ContextProvider.jsx";

export default function SignUp() {
    const loginRef = useRef();
    const emailRef = useRef();
    const passwordRef = useRef();
    const passwordConfirmationRef = useRef();
    const {setUser, setToken} = useStateContext()
    const [errors, setErrors] = useState(null)

    const onSubmit = (ev) => {
        ev.preventDefault()
        const payload = {
            login: loginRef.current.value,
            email: emailRef.current.value,
            password: passwordRef.current.value,
            password_confirmation: passwordConfirmationRef.current.value,
        }
        console.log(payload);
        axiosClient.post('/signup', payload)
            .then(({data}) => {
                setUser(data.user)
                setToken(data.token)
            })
            .catch(err => {
                const response = err.response;
                if (response && response.status === 422) {
                    console.log(response.data.errors);
                    setErrors(response.data.errors)
                }
            })
    }

    return (
        <div className="login-container">
            <div className="login-box">
                <form onSubmit={onSubmit}>
                <h2 className="login-title">Регистрация</h2>
                    {errors && <div className="alert">
                        {Object.keys(errors).map(key => (
                            <p key={key}>{errors[key][0]}</p>
                        ))}
                    </div>
                    }
                <input ref={loginRef} type="text" placeholder="Логин" className="login-input" />
                <input ref={emailRef} type="email" placeholder="Email" className="login-input" />
                <input ref={passwordRef} type="password" placeholder="Пароль" className="login-input" />
                <input ref={passwordConfirmationRef} type="password" placeholder="Подтверждение пароля" className="login-input" />
                <button className="login-button">Продолжить</button>
                <p className="register-text">
                    Есть аккаунт? <Link to="/login" className="register-link">Вход</Link>
                </p>
                </form>
            </div>
        </div>
    );
}
