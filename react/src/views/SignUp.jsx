import { useState, useRef } from "react";
import axiosClient from "../axios-client";
import { useStateContext } from "../context/ContextProvider";

export default function SignUp() {
    const loginRef = useRef();
    const emailRef = useRef();
    const passwordRef = useRef();
    const passwordConfirmationRef = useRef();
    const { setUser, setToken } = useStateContext();

    const [errors, setErrors] = useState(null);  // Ошибки для страницы
    const [modalErrors, setModalErrors] = useState(null); // Ошибки для модального окна
    const [isCodeSent, setIsCodeSent] = useState(false);
    const [codeInput, setCodeInput] = useState("");
    const [timer, setTimer] = useState(60);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const startTimer = () => {
        setTimer(60);
        const interval = setInterval(() => {
            setTimer((prev) => {
                if (prev === 1) clearInterval(interval);
                return prev - 1;
            });
        }, 1000);
    };

    const sendCode = () => {
        const email = emailRef.current.value;
        axiosClient
            .post("/send-mail-for-code-confirmation", { email })
            .then(() => {
                setIsCodeSent(true);
                startTimer();
                setModalErrors(null);  // Очистить ошибки в модальном окне при отправке кода
            })
            .catch((err) => {
                if (err.response && err.response.data && err.response.data.message) {
                    setModalErrors({ message: err.response.data.message });
                } else {
                    setModalErrors({ message: "Ошибка при отправке кода. Попробуйте позже." });
                }
            });
    };

    const validateUserData = () => {
        const payload = {
            login: loginRef.current.value,
            email: emailRef.current.value,
            password: passwordRef.current.value,
            password_confirmation: passwordConfirmationRef.current.value,
        };

        axiosClient
            .post("/validate-signup", payload)
            .then(() => sendCode())
            .catch((err) => {
                if (err.response && err.response.data && err.response.data.message) {
                    setErrors({ message: err.response.data.message });  // Ошибка на странице
                } else {
                    setErrors({ message: "Ошибка при валидации данных." });  // Ошибка на странице
                }
            });
    };

    const verifyCode = () => {
        setIsSubmitting(true);
        axiosClient
            .post("/confirm-code", { input_code: codeInput })
            .then(({ data }) => {
                if (data.status === "success") {
                    const payload = {
                        login: loginRef.current.value,
                        email: emailRef.current.value,
                        password: passwordRef.current.value,
                        password_confirmation: passwordConfirmationRef.current.value,
                    };
                    axiosClient
                        .post("/signup", payload)
                        .then(({ data }) => {
                            setUser(data.user);
                            setToken(data.token);
                            localStorage.setItem("ACCESS_TOKEN", data.token);
                            localStorage.setItem("EXPIRES_AT", data.expires_at);
                        })
                        .catch((err) => {
                            setModalErrors(err.response?.data?.errors || { message: "Произошла ошибка." });
                        });
                } else {
                    setModalErrors({ message: "Неверный код подтверждения." });
                }
            })
            .catch(() => {
                setModalErrors({ message: "Ошибка при верификации кода." });
            })
            .finally(() => {
                setIsSubmitting(false);
            });
    };

    // Функция для закрытия модального окна
    const closeModal = () => {
        setIsCodeSent(false);
        setCodeInput("");
        setModalErrors(null); // Очистить ошибки при закрытии окна
    };

    return (
        <div className="login-container">
            <div className="login-box">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        validateUserData();
                    }}
                >
                    <h2 className="login-title">Регистрация</h2>
                    {errors && <div className="alert">{errors.message}</div>} {/* Ошибки на странице */}
                    <input ref={loginRef} type="text" placeholder="Логин" className="login-input" />
                    <input ref={emailRef} type="email" placeholder="Email" className="login-input" />
                    <input ref={passwordRef} type="password" placeholder="Пароль" className="login-input" />
                    <input ref={passwordConfirmationRef} type="password" placeholder="Подтверждение пароля" className="login-input" />
                    <button className="login-button">Продолжить</button>
                </form>
            </div>

            {/* Модальное окно для ввода кода */}
            {isCodeSent && (
                <div className="modal">
                    <div className="modal-content">
                        {/* Кнопка для закрытия модального окна (в правом верхнем углу) */}
                        <button type="button" onClick={closeModal} className="close-modal-button">
                            <span>&times;</span> {/* Иконка крестика */}
                        </button>

                        {/* Ошибка при неверном коде (выводим её над полем ввода) */}
                        {modalErrors && <div className="alert error-above-input">{modalErrors.message}</div>}

                        <h3>Введите код подтверждения</h3>
                        <input
                            type="text"
                            value={codeInput}
                            onChange={(e) => setCodeInput(e.target.value)}
                            placeholder="Введите код"
                        />
                        <button onClick={verifyCode} className="verify-button" disabled={isSubmitting}>
                            Зарегистрироваться
                        </button>
                        <div>
                            {timer > 0 ? (
                                <p>Повторная отправка через: {timer} секунд</p>
                            ) : (
                                <button onClick={sendCode}>Отправить код повторно</button>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
