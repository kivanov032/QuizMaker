import { useState, useEffect } from "react";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function EmailConfirmation() {
    const { setUser, setToken } = useStateContext();
    const [code, setCode] = useState('');
    const [message, setMessage] = useState('');
    const [errors, setErrors] = useState('');
    const [resendTimeout, setResendTimeout] = useState(60);
    const email = localStorage.getItem('SIGNUP_EMAIL');

    useEffect(() => {
        if (resendTimeout > 0) {
            const timer = setTimeout(() => setResendTimeout(resendTimeout - 1), 1000);
            return () => clearTimeout(timer);
        }
    }, [resendTimeout]);

    const confirmCode = () => {
        axiosClient.post('/confirm-code', { input_code: code })
            .then(({ data }) => {
                axiosClient.post('/signup-confirmed', { email }) // отдельный endpoint
                    .then(({ data }) => {
                        setUser(data.user);
                        setToken(data.token);
                        localStorage.setItem('ACCESS_TOKEN', data.token);
                        localStorage.setItem('EXPIRES_AT', data.expires_at);
                    });
            })
            .catch(err => {
                setErrors(err.response?.data?.message || 'Ошибка');
            });
    };

    const resendCode = () => {
        if (resendTimeout === 0) {
            axiosClient.post('/send-mail-for-code-confirmation', { email })
                .then(({ data }) => {
                    setMessage(data.message);
                    setResendTimeout(60);
                });
        }
    };

    return (
        <div className="login-container">
            <div className="login-box">
                <h2>Подтверждение почты</h2>
                <input
                    type="text"
                    maxLength={6}
                    placeholder="Введите код из письма"
                    className="login-input"
                    value={code}
                    onChange={e => setCode(e.target.value)}
                />
                <button className="login-button" onClick={confirmCode}>Подтвердить</button>
                <p>{errors && <span>{errors}</span>}</p>
                <p>{message && <span>{message}</span>}</p>
                <button
                    className="login-button"
                    onClick={resendCode}
                    disabled={resendTimeout > 0}
                >
                    {resendTimeout > 0 ? `Отправить повторно через ${resendTimeout} сек` : 'Отправить повторно'}
                </button>
            </div>
        </div>
    );
}
