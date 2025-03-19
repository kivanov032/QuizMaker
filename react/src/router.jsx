import { createBrowserRouter, Navigate } from "react-router-dom";
import NotFound from "./views/NotFound.jsx";
import CreatorQuestion from "./views/CreatorQuestion.jsx";
import CreatorLayout from "./components/CreatorLayout.jsx";
import GuestLayout from "./components/GuestLayout.jsx";
import MainLayout from "./components/MainLayout.jsx";
import Login from "./views/Login.jsx";
import SignUp from "./views/SignUp.jsx";
import MainPage from "./views/MainPage.jsx";

const router = createBrowserRouter([
    // {
    //     path: '/createQuestion',
    //     element: <CreatorLayout />,
    //     children: [
    //         {
    //             path: '/',
    //             element: <Navigate to="/createQuestion/1" /> // Перенаправляем на первый вопрос
    //         },
    //         {
    //             path: '/createQuestion/:id', // Добавляем параметр id
    //             element: <CreatorQuestion />
    //         },
    //     ]
    // },
    {
        path: '/',
        element: <GuestLayout />,
        children:[
            {
                path: '/login',
                element: <Login />
            },
            {
                path: '/signup',
                element: <SignUp />
            },
        ]
    },
    {
        path: '/',
        element: <MainLayout />,
        children:[
            {
                path: '/',
                index: true,
                element: <MainPage />
            },
            {
                path: '/createQuestion',
                element: <CreatorLayout />,
                children: [
                    {
                        path: '/createQuestion/:id', // Добавляем параметр id
                        element: <CreatorQuestion />
                    },
                ]
            },
        ]
    },
    {
        path: '*',
        element: <NotFound />
    },
]);

export default router;
