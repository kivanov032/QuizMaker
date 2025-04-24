import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import router from './router.jsx';
import './index.css'
//import {StrictMode} from "react";
import { QuestionProvider } from './context/QuestionContext.jsx';
import {ContextProvider} from "./context/ContextProvider.jsx";
import {QuizProvider} from "./context/QuizContext.jsx";

createRoot(document.getElementById('root')).render(
        <ContextProvider>
            <QuestionProvider>
                <QuizProvider>
                    <RouterProvider router={router} />
                </QuizProvider>
            </QuestionProvider>
        </ContextProvider>

);
