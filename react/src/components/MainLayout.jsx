import { Navigate, Outlet, useNavigate } from 'react-router-dom';
import { useStateContext } from '../context/ContextProvider.jsx';
import axiosClient from '../axios-client.js';
import { useEffect, useRef, useState } from 'react';

export default function MainLayout() {
    const { user, token, setUser, setToken, getStoredToken } = useStateContext();
    const navigate = useNavigate();
    const intervalRef = useRef(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        console.log('Я в useEffect в MainLayout');

        const checkAndExtendToken = async () => {
            const storedToken = getStoredToken();
            if (!storedToken) {
                navigate('/login');
                return;
            }

            try {
                const { data } = await axiosClient.get('/user');
                console.log('Я в useEffect в checkToken');
                setUser(data.user);
                setToken(storedToken); // токен остаётся тем же
            } catch (error) {
                console.error('Ошибка при проверке токена:', error);
                handleLogout();
            } finally {
                setLoading(false); // Проверка завершилась
            }
        };

        checkAndExtendToken();

        // запускаем интервал только после успешного входа
        // if (!intervalRef.current && token) {
        //     intervalRef.current = setInterval(async () => {
        //         try {
        //             const { data } = await axiosClient.get('/check-and-extend-token');
        //             if (data.token) {
        //                 setToken(data.token);
        //             }
        //         } catch (error) {
        //             console.warn("Не удалось продлить токен:", error);
        //             if (error.response?.status === 401) {
        //                 handleLogout();
        //             }
        //         }
        //     }, 5 * 60 * 1000);
        // }

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
                intervalRef.current = null;
            }
        };
    }, []);

    const onLogout = async (ev) => {
        ev.preventDefault();
        try {
            await axiosClient.post('/logout');
        } catch (error) {
            console.error('Ошибка при выходе:', error);
        } finally {
            handleLogout();
        }
    };

    const handleLogout = () => {
        setUser({});
        setToken(null);
        localStorage.removeItem('ACCESS_TOKEN');
        localStorage.removeItem('USER_DATA');
        navigate('/login');
    };

    const handleMainLayoutClick = () => {
        navigate('/');
    };

    // 🟡 Пока загружаемся — ничего не показываем
    if (loading) {
        return <div>Загрузка...</div>; // можно заменить на спиннер
    }

    // После загрузки — если всё-таки нет токена — редиректим
    if (!token) {
        return <Navigate to="/login" />;
    }

    return (
        <div>
            <header className="header">
                <div className="header-left">
                    <span className="clickable-main" onClick={handleMainLayoutClick}>
                        МЕНЮ
                    </span>
                </div>
                <div className="header-right">
                    <span className="clickable">{user.login}</span>
                    <span onClick={onLogout} className="clickable logout">
                        Выйти
                    </span>
                </div>
            </header>
            <main className="main-content">
                <Outlet />
            </main>
        </div>
    );
}
