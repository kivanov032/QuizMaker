import { createContext, useContext, useState, useEffect  } from "react";

const StateContext = createContext({
    user: null,
    token: null,
    setUser: () => {},
    setToken: () => {},
    getStoredToken: () => {},
});

// eslint-disable-next-line react/prop-types
export const ContextProvider = ({ children }) => {

    const [user, setUser] = useState(() => JSON.parse(localStorage.getItem('USER_DATA')) || {});
    const [token, setToken] = useState();

    // Функция для получения токена из localStorage
    const getStoredToken = () => {
        return localStorage.getItem('ACCESS_TOKEN');
    };

    useEffect(() => {
        if (user && token) {
            // Сохраняем данные пользователя в localStorage при изменении
            localStorage.setItem('USER_DATA', JSON.stringify(user));
            localStorage.setItem('ACCESS_TOKEN', token);
        }
    }, [user, token]); // Срабатывает при изменении user или token

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

// eslint-disable-next-line react-refresh/only-export-components
export const useStateContext = () => useContext(StateContext);
