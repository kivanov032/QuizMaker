import { Navigate, Outlet, useNavigate } from "react-router-dom";
import { useStateContext } from "../context/ContextProvider.jsx";
import axiosClient from "../axios-client.js";
import { useEffect } from "react";

export default function MainLayout() {
    const { user, token, setUser, setToken, getStoredToken } = useStateContext();
    const navigate = useNavigate();

    useEffect(() => {
        console.log("Я в useEffect в MainLayout");

        // Проверяем, есть ли пользователь и токен в контексте
        if (!user || !token) {
            const storedToken = getStoredToken(); // Получаем токен из localStorage
            if (storedToken) {
                const checkToken = async () => {
                    try {
                        const { data } = await axiosClient.get('/user'); // Токен будет добавлен автоматически
                        setUser(data.user);
                        setToken(data.token);
                    } catch (error) {
                        console.error("Ошибка при проверке токена:", error);
                        handleLogout();
                    }
                };

                checkToken(); // Вызов функции для проверки токена
            } else {
                navigate('/login'); // Перенаправление на страницу входа, если токен отсутствует
            }
        }
    }, [navigate, setToken, setUser, user, token, getStoredToken]);

    // Обработчик выхода
    const onLogout = async (ev) => {
        ev.preventDefault();

        try {
            await axiosClient.post('/logout');
        } catch (error) {
            console.error("Ошибка при выходе:", error);
        } finally {
            handleLogout(); // Вызов общей логики выхода
        }
    };


    // Общая логика выхода
    const handleLogout = () => {
        setUser({});
        setToken(null);
        localStorage.removeItem('ACCESS_TOKEN');
        navigate('/login'); // Перенаправление после выхода
    };

    // Перенаправление на страницу входа, если токен отсутствует
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
                    <span className="clickable-main" onClick={handleMainLayoutClick}>МЕНЮ</span>
                </div>

                <div className="header-right">
                    <span className="clickable">{user.login}</span>
                    <span onClick={onLogout} className="clickable logout">Выйти</span>
                </div>
            </header>

            {/* Основное содержимое */}
            <main className="main-content">
                <Outlet />
            </main>
        </div>
    );
}
