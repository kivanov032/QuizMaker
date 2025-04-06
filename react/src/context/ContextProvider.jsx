import { createContext, useContext, useState } from "react";

const StateContext = createContext({
    user: null,
    token: null,
    setUser: () => {},
    setToken: () => {},
    getStoredToken: () => {},
});

export const ContextProvider = ({ children }) => {
    const [user, setUser] = useState({});
    const [token, setToken] = useState(null);

    // Функция для получения токена из localStorage
    const getStoredToken = () => {
        return localStorage.getItem('ACCESS_TOKEN');
    };

    return (
        <StateContext.Provider value={{
            user,
            token,
            setUser,
            setToken,
            getStoredToken,
        }}>
            {children}
        </StateContext.Provider>
    );
};

export const useStateContext = () => useContext(StateContext);
