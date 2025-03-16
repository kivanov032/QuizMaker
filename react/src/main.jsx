import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import router from './router.jsx';
import './index.css'
import {StrictMode} from "react";
import { QuestionProvider } from './context/QuestionContext.jsx';
import {ContextProvider} from "./context/ContextProvider.jsx";

createRoot(document.getElementById('root')).render(
    <StrictMode>
        <ContextProvider>
            <QuestionProvider>
                <RouterProvider router={router} />
            </QuestionProvider>
        </ContextProvider>
    </StrictMode>
);
