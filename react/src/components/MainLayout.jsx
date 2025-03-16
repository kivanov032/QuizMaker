import {Navigate, Outlet} from "react-router-dom";
import {useStateContext} from "../context/ContextProvider.jsx";
//import React from "react";

export default function GuestLayout() {
    const {token}  = useStateContext()

    if (!token) {
        return <Navigate to="/login" />
    }

    return (
        <div>
            {/* Верхняя панель */}
            <header className="header">
                <div className="header-left">MainLayout</div>
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
