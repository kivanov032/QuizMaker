import { createBrowserRouter, Navigate } from "react-router-dom";
import NotFound from "./views/NotFound.jsx";
import CreatorQuestion from "./views/CreatorQuestion.jsx";
import CreatorLayout from "./components/CreatorLayout.jsx";

const router = createBrowserRouter([
    {
        path: '/',
        element: <CreatorLayout />,
        children: [
            {
                path: '/',
                element: <Navigate to="/createQuestion/1" /> // Перенаправляем на первый вопрос
            },
            {
                path: '/createQuestion/:id', // Добавляем параметр id
                element: <CreatorQuestion />
            },
        ]
    },
    {
        path: '*',
        element: <NotFound />
    },
]);

export default router;
