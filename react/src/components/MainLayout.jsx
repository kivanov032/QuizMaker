import {Navigate, Outlet, useNavigate} from "react-router-dom";
import {useStateContext} from "../context/ContextProvider.jsx";
import axiosClient from "../axios-client.js";
import {useEffect} from "react";
//import React from "react";

export default function MainLayout() {
    const {user, token, setUser, setToken}  = useStateContext()
    const navigate = useNavigate(); // Хук для навигации
    useEffect(() => {
        axiosClient.get('/user')
            .then(({data}) => {
                setUser(data)
            })
    }, []);

    if (!token) {
        return <Navigate to="/login" />
    }


    const onLogout = (ev) => {
        ev.preventDefault()

        axiosClient.post('/logout')
            .then(() => {
                setUser({})
                setToken(null)
            })
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
                    <span className="clickable" onClick={handleMainLayoutClick}>МЕНЮ</span>
                </div>

                <div className="header-right">
                    <span className="clickable">{user.login}</span>
                    <span onClick={onLogout} className="clickable logout">Logout</span>
                </div>
            </header>

            {/* Основное содержимое */}
            <main className="main-content">
                <Outlet />
            </main>
        </div>
    );

}
