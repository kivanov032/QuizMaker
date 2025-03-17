import { createContext, useState } from 'react';

// Контекст для вопросов
export const QuestionContext = createContext();

// Провайдер для управления состоянием вопросов
export const QuestionProvider = ({ children }) => {
    const [questions, setQuestions] = useState([]);

    const updateQuestion = (id, question, answers, correctAnswerIndex) => {
        const existingQuestionIndex = questions.findIndex(q => q.id === id);
        if (existingQuestionIndex !== -1) {
            const updatedQuestions = [...questions];
            updatedQuestions[existingQuestionIndex] = { id, question, answers, correctAnswerIndex };
            setQuestions(updatedQuestions);
        }
    };

    const addQuestion = (newQuestion) => {
        const newId = questions.length > 0 ? Math.max(...questions.map((q) => q.id)) + 1 : 1;
        setQuestions([...questions, { id: newId, ...newQuestion }]);
    };

    const getQuestion = (id) => {
        return questions.find(q => q.id === id);
    };

    const deleteQuestion = (id) => {
        console.log("Удаляем вопрос с id:", id);
        const updatedQuestions = questions.filter(q => q.id !== id);

        // Обновляем идентификаторы
        const reindexedQuestions = updatedQuestions.map((q, index) => ({
            ...q,
            id: index + 1 // Переопределяем идентификаторы
        }));

        setQuestions(reindexedQuestions);
    };


    return (
        <QuestionContext.Provider value={{ questions, setQuestions, updateQuestion, getQuestion, deleteQuestion, addQuestion }}>
            {children}
        </QuestionContext.Provider>
    );

};
