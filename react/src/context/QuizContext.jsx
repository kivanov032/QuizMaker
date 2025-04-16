import { createContext, useContext, useState, useEffect } from "react";

const QuizContext = createContext({
    quiz: null,
    setQuiz: () => {},
    resetQuiz: () => {},
    quizQuestions: [],
    setQuizQuestions: () => {},
    resetQuestions: () => {},
    userAnswers: {},
    setUserAnswers: () => {},
    resetUserAnswers: () => {},
});

// eslint-disable-next-line react/prop-types
export const QuizProvider = ({ children }) => {
    const [quiz, setQuiz] = useState(() => {
        const savedQuiz = localStorage.getItem('Quiz');
        return savedQuiz ? JSON.parse(savedQuiz) : null;
    });

    const [quizQuestions, setQuizQuestions] = useState([]);
    const [userAnswers, setUserAnswers] = useState(() => {
        const savedAnswers = localStorage.getItem('QuizAnswers');
        return savedAnswers ? JSON.parse(savedAnswers) : {};
    });

    const updateQuiz = (quizData) => {
        if (!quizData) return;
        setQuiz(quizData);
    };

    const resetQuiz = () => {
        setQuiz(null);
        resetQuestions();
        resetUserAnswers();
    };

    const resetQuestions = () => setQuizQuestions([]);
    const resetUserAnswers = () => setUserAnswers({});

    useEffect(() => {
        if (quiz) {
            localStorage.setItem('Quiz', JSON.stringify(quiz));
        } else {
            localStorage.removeItem('Quiz');
        }
    }, [quiz]);

    useEffect(() => {
        localStorage.setItem('QuizAnswers', JSON.stringify(userAnswers));
    }, [userAnswers]);

    return (
        <QuizContext.Provider value={{
            quiz,
            setQuiz: updateQuiz,
            resetQuiz,
            quizQuestions,
            setQuizQuestions,
            resetQuestions,
            userAnswers,
            setUserAnswers,
            resetUserAnswers,
        }}>
            {children}
        </QuizContext.Provider>
    );
};

// eslint-disable-next-line react-refresh/only-export-components
export const useQuizContext = () => useContext(QuizContext);
