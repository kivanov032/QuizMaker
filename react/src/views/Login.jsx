import { Link } from "react-router-dom";
import {useRef, useState} from "react";
import {useStateContext} from "../context/ContextProvider.jsx";
import axiosClient from "../axios-client.js";

export default function Login() {

    const loginRef = useRef();
    const passwordRef = useRef();
    const {setUser, setToken} = useStateContext();
    const [errors, setErrors] = useState(null)

    const [showPassword, setShowPassword] = useState(false); // Открыт/закрыт глаз

    const [loading, setLoading] = useState(false); // Загрузка

    // Запрос на авторизацию
    const onSubmit = (ev) => {
        ev.preventDefault();
        const payload = {
            login: loginRef.current.value,
            password: passwordRef.current.value,
        };
        setLoading(true);
        setErrors(null);
        axiosClient
            .post('/login', payload)
            .then(({ data }) => {
                setUser(data.user);
                setToken(data.token);
                localStorage.setItem('ACCESS_TOKEN', data.token);
                localStorage.setItem('EXPIRES_AT', data.expires_at);
            })
            .catch(err => {
                setLoading(false);
                if (err.message === 'Network Error') {
                    setErrors({ login: ['Технические проблемы с сервером. Пожалуйста, попробуйте позже.'] });
                } else {
                    const response = err.response;
                    console.log(err.response.data.message);
                    if (response && response.status === 422) {
                        if (response.data.errors) {
                            const firstErrorKey = Object.keys(response.data.errors)[0];
                            const firstErrorMessage = response.data.errors[firstErrorKey][0];
                            setErrors({[firstErrorKey]: [firstErrorMessage]});
                        } else {
                            setErrors({ login: [response.data.message] });
                        }
                    } else {
                        setErrors({ login: ['Неизвестная ошибка'] });
                    }
                }
            });
    };


    return (
        <div className="login-container">
            <div className="login-box">
                <form onSubmit={onSubmit}>
                <h2 className="login-title">Вход</h2>

                    {!errors && loading && <div className="loading-text show">Загрузка...</div>}

                    {errors && <div className="alert">
                    {Object.keys(errors).map(key => (
                        <p key={key}>{errors[key][0]}</p>
                    ))}
                </div>
                }
                <input ref={loginRef} type="text" placeholder="Login" className="login-input" />
                    {/* Поле пароля со встроенным глазком */}
                    <div className="inline-password-container">
                        <input
                            ref={passwordRef}
                            type={showPassword ? "text" : "password"}
                            placeholder="Пароль"
                            className="login-input with-eye"
                        />
                        <button
                            type="button"
                            className="inline-eye-toggle"
                            onClick={() => setShowPassword(!showPassword)}
                            aria-label={showPassword ? "Скрыть пароль" : "Показать пароль"}
                        >
                            <span className={showPassword ? "" : "eye-crossed"}>
                              👁️‍🗨️
                            </span>
                        </button>
                    </div>

                <button className="login-button">Войти</button>
                <p className="register-text">
                    Нет аккаунта? <Link to="/signup" className="register-link">Регистрация</Link>
                </p>
                </form>
            </div>
        </div>
    );
}
