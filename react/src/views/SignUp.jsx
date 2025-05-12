import { useState, useRef } from 'react';
import axiosClient from '../axios-client';
import { useStateContext } from '../context/ContextProvider';
import { Link } from 'react-router-dom';

export default function SignUp() {
    const timeForConfirmationCode = 120; // Время (в секундах) отправки кода подтверждения

    const loginRef = useRef();
    const emailRef = useRef();
    const passwordRef = useRef();
    const passwordConfirmationRef = useRef();
    const { setUser, setToken } = useStateContext();

    const [errors, setErrors] = useState(null); // Ошибки для страницы
    const [modalErrors, setModalErrors] = useState(null); // Ошибки для модального окна
    const [isCodeSent, setIsCodeSent] = useState(false); // Метка на открытие модального окна
    const [codeInput, setCodeInput] = useState(''); // Ввод кода подтверждения
    const [isSubmitting, setIsSubmitting] = useState(false); // Кнопка для повторной отправки кода подтверждения

    // Состояния для отображения/скрытия пароля
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const [loading, setLoading] = useState(false); // Загрузка

    const [timer, setTimer] = useState(timeForConfirmationCode); // Таймер
    const timerIntervalRef = useRef(null); // ID интервала

    // Таймер на ввод кода подтверждения
    const startTimer = () => {
        // Очищаем предыдущий интервал
        if (timerIntervalRef.current) {
            clearInterval(timerIntervalRef.current);
        }

        setTimer(timeForConfirmationCode);
        timerIntervalRef.current = setInterval(() => {
            setTimer((prev) => {
                if (prev <= 1) {
                    clearInterval(timerIntervalRef.current);
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
    };

    // Отправка кода подтверждения
    const sendCode = () => {
        setLoading(true);
        setCodeInput('');
        const email = emailRef.current.value;
        axiosClient
            .post('/send-mail-for-code-confirmation', { email })
            .then(() => {
                setErrors(null); // Очистка окна с ошибками
                setModalErrors(null); // Очистка модального окна
                setIsCodeSent(true); // Установка метки на открытие модального окна
                setLoading(false); // Убираем загрузку
                startTimer(); // Запускаем таймер
            })
            .catch((err) => {
                setErrors(null); // Очистка окна с ошибками
                setLoading(false); // Убираем загрузку
                if (err.response && err.response.data && err.response.data.message) {
                    setModalErrors({ message: err.response.data.message });
                } else {
                    setModalErrors({
                        message: 'Ошибка при отправке кода. Попробуйте позже.',
                    });
                }
            });
    };

    // Запрос на валидацию данных
    const validateUserData = () => {
        const payload = {
            login: loginRef.current.value,
            email: emailRef.current.value,
            password: passwordRef.current.value,
            password_confirmation: passwordConfirmationRef.current.value,
        };

        setErrors(false);
        setLoading(true);

        axiosClient
            .post('/validate-signup', payload)
            .then(() => sendCode())
            .catch((err) => {
                setLoading(false);
                if (err.message === 'Network Error') {
                    setErrors({
                        message: ['Технические проблемы с сервером. Пожалуйста, попробуйте позже.'],
                    });
                } else {
                    const response = err.response;
                    if (response && response.status === 422) {
                        setErrors({ message: [response.data.message] });
                    } else {
                        setErrors({ message: ['Произошла неизвестная ошибка'] });
                    }
                }
            });
    };

    // Запрос верификацию кода подтверждения
    const verifyCode = () => {
        setModalErrors(false);
        setLoading(true);
        setIsSubmitting(true);
        if (timer <= 0) {
            setModalErrors({
                message: ['Время ожидания закончилось. Пожалуйста, запросите новый код.'],
            });
            return;
        }
        axiosClient
            .post('/confirm-code', {
                input_code: codeInput,
                email: emailRef.current.value,
            })
            .then(({ data }) => {
                if (data.status === 'success') {
                    const payload = {
                        login: loginRef.current.value,
                        email: emailRef.current.value,
                        password: passwordRef.current.value,
                        password_confirmation: passwordConfirmationRef.current.value,
                    };
                    axiosClient
                        .post('/signup', payload)
                        .then(({ data }) => {
                            setUser(data.user);
                            setToken(data.token);
                            localStorage.setItem('ACCESS_TOKEN', data.token);
                            localStorage.setItem('EXPIRES_AT', data.expires_at);
                        })
                        .catch((err) => {
                            setModalErrors(
                                err.response?.data?.errors || { message: 'Произошла ошибка.' },
                            );
                        });
                } else {
                    setModalErrors({ message: 'Неверный код подтверждения.' });
                }
            })
            .catch((err) => {
                setLoading(false);
                if (err.message === 'Network Error') {
                    setModalErrors({
                        message: ['Технические проблемы с сервером. Пожалуйста, попробуйте позже.'],
                    });
                } else {
                    const response = err.response;
                    setModalErrors({ message: [response.data.message] });
                }
            })
            .finally(() => {
                setIsSubmitting(false);
            });
    };

    // Функция для закрытия модального окна
    const closeModal = () => {
        // Очищаем интервал при закрытии
        if (timerIntervalRef.current) {
            clearInterval(timerIntervalRef.current);
            timerIntervalRef.current = null;
        }
        setIsCodeSent(false);
        setCodeInput('');
        setModalErrors(null);
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
                    {errors && <div className="alert">{errors.message}</div>}{' '}
                    {/* Ошибки на странице */}
                    {!errors && !isCodeSent && loading && (
                        <div className="loading-text show">Загрузка...</div>
                    )}
                    <input ref={loginRef} type="text" placeholder="Логин" className="login-input" />
                    <input
                        ref={emailRef}
                        type="email"
                        placeholder="Email"
                        className="login-input"
                    />
                    {/* Поле пароля со встроенным глазком */}
                    <div className="inline-password-container">
                        <input
                            ref={passwordRef}
                            type={showPassword ? 'text' : 'password'}
                            placeholder="Пароль"
                            className="login-input with-eye"
                        />
                        <button
                            type="button"
                            className="inline-eye-toggle"
                            onClick={() => setShowPassword(!showPassword)}
                            aria-label={showPassword ? 'Скрыть пароль' : 'Показать пароль'}
                        >
                            <span className={showPassword ? '' : 'eye-crossed'}>👁️‍🗨️</span>
                        </button>
                    </div>
                    {/* Поле подтверждения пароля со встроенным глазком */}
                    <div className="inline-password-container">
                        <input
                            ref={passwordConfirmationRef}
                            type={showConfirmPassword ? 'text' : 'password'}
                            placeholder="Подтверждение пароля"
                            className="login-input with-eye"
                        />
                        <button
                            type="button"
                            className="inline-eye-toggle"
                            onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                            aria-label={showConfirmPassword ? 'Скрыть пароль' : 'Показать пароль'}
                        >
                            <span className={showConfirmPassword ? '' : 'eye-crossed'}>👁️‍🗨️</span>
                        </button>
                    </div>
                    <button className="login-button">Продолжить</button>
                    <p className="register-text">
                        Есть аккаунт?
                        <Link to="/login" className="register-link">
                            Войти
                        </Link>
                    </p>
                </form>
            </div>

            {/* Модальное окно для ввода кода */}
            {isCodeSent && (
                <div className="modal">
                    <div className="modal-content">
                        {/* Кнопка для закрытия модального окна (в правом верхнем углу) */}
                        <span className="close" onClick={closeModal}>
                            &times;
                        </span>
                        <h2 className="login-title">Введите код подтверждения</h2>
                        {modalErrors && <div className="alert">{modalErrors.message}</div>}{' '}
                        {/* Ошибки на странице */}
                        {!modalErrors && loading && (
                            <div className="loading-text show">Загрузка...</div>
                        )}
                        <input
                            type="text"
                            value={codeInput}
                            onChange={(e) => setCodeInput(e.target.value)}
                            className="login-input"
                            placeholder="Введите код"
                        />
                        <button
                            onClick={verifyCode}
                            className="login-button"
                            disabled={isSubmitting}
                        >
                            Подтвердить
                        </button>
                        <div>
                            {timer > 0 ? (
                                <p>Повторная отправка через: {timer} секунд</p>
                            ) : (
                                <button onClick={sendCode} className="login-button">
                                    Отправить код повторно
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
