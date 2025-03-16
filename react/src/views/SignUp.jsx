import { Link } from "react-router-dom";

export default function SignUp() {
    return (
        <div className="login-container">
            <div className="login-box">
                <h2 className="login-title">Регистрация</h2>
                <input type="text" placeholder="Логин" className="login-input" />
                <input type="email" placeholder="Email" className="login-input" />
                <input type="password" placeholder="Пароль" className="login-input" />
                <input type="password" placeholder="Подтверждение пароля" className="login-input" />
                <button className="login-button">Продолжить</button>
                <p className="register-text">
                    Есть аккаунт? <Link to="/login" className="register-link">Вход</Link>
                </p>
            </div>
        </div>
    );
}
