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
        console.log("Вопросы до удаления:", questions);

        // Логируем идентификаторы всех вопросов
        if (questions) {
            questions.forEach(q => console.log("id вопроса:", q.id));
        }

        const updatedQuestions = questions?.filter(q => q.id !== id).map((q, index) => ({
            ...q,
            id: index + 1
        }));

        console.log("Вопросы после удаления:", updatedQuestions);
        setQuestions(updatedQuestions);

        if (updatedQuestions?.length === questions?.length) {
            console.warn("Вопрос не был удален. Проверьте id:", id);
        } else {
            console.log("Вопрос успешно удален. Новое количество вопросов:", updatedQuestions?.length);
        }
    };

    return (
        <QuestionContext.Provider value={{ questions, updateQuestion, getQuestion, deleteQuestion, addQuestion }}>
            {children}
        </QuestionContext.Provider>
    );
};
