import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import router from './router.jsx';
import './index.css'
import {StrictMode} from "react";
import { QuestionProvider } from './context/QuestionContext.jsx';

createRoot(document.getElementById('root')).render(
    <StrictMode>
        <QuestionProvider>
            <RouterProvider router={router} />
        </QuestionProvider>
    </StrictMode>
);
