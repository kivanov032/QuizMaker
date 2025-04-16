import { createBrowserRouter } from "react-router-dom";
import NotFound from "./views/NotFound.jsx";
import CreatorQuestion from "./views/CreatorQuestion.jsx";
import CreatorLayout from "./components/CreatorLayout.jsx";
import GuestLayout from "./components/GuestLayout.jsx";
import MainLayout from "./components/MainLayout.jsx";
import Login from "./views/Login.jsx";
import SignUp from "./views/SignUp.jsx";
import MainPage from "./views/MainPage.jsx";
import PassQuizLayout from "./components/PassQuizLayout.jsx";
import PassQuizQuestion from "./views/PassQuizQuestion.jsx";
import QuizResults from "./views/QuizResults.jsx";

const router = createBrowserRouter([
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
                        path: '/createQuestion/:id',
                        element: <CreatorQuestion />
                    },
                ]
            },
            {
                path: '/passQuiz',
                element: <PassQuizLayout />,
                children: [
                    {
                        path: '/passQuiz/:id',
                        element: <PassQuizQuestion />
                    },
                    {
                        path: '/passQuiz/quizResult',
                        element: <QuizResults />
                    },
                ],
            },
        ]
    },
    {
        path: '*',
        element: <NotFound />
    },
]);

export default router;
