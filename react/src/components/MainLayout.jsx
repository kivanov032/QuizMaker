import { Navigate, Outlet, useNavigate } from "react-router-dom";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function GuestLayout() {
    const { token } = useStateContext();
    const navigate = useNavigate(); // Хук для навигации

    // Если токен отсутствует, перенаправляем на страницу входа
    if (!token) {
        return <Navigate to="/login" />;
    }

    // Обработчик клика для перенаправления на главную страницу
    const handleMainLayoutClick = () => {
        navigate("/");
    };

    return (
        <div>
            {/* Верхняя панель */}
            <header className="header">
                <div className="header-left">
                    {/* Добавляем обработчик клика */}
                    <span className="clickable" onClick={handleMainLayoutClick}>
                        МЕНЮ
                    </span>
                </div>
                <div className="header-right">
                    <span className="clickable">User info</span>
                    <span className="clickable logout">Logout</span>
                </div>
            </header>

            {/* Основное содержимое */}
            <main className="main-content">
                <Outlet />
            </main>
        </div>
    );
}
