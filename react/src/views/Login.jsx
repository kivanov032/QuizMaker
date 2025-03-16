import { Link } from "react-router-dom";

export default function Login() {
    return (
        <div className="login-container">
            <div className="login-box">
                <h2 className="login-title">Вход</h2>
                <input type="email" placeholder="Email" className="login-input" />
                <input type="password" placeholder="Пароль" className="login-input" />
                <button className="login-button">Войти</button>
                <p className="register-text">
                    Нет аккаунта? <Link to="/signup" className="register-link">Регистрация</Link>
                </p>
            </div>
        </div>
    );
}
