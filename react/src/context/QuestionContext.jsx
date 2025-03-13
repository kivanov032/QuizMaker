import { createContext, useState } from 'react';

// Контекст для вопросов
export const QuestionContext = createContext();

// Провайдер для управления состоянием вопросов
export const QuestionProvider = ({ children }) => {
    const [questions, setQuestions] = useState([]);

    const updateQuestion = (id, question, answers, correctAnswerIndex) => {
        const existingQuestionIndex = questions.findIndex(q => q.id === id);
        const updatedQuestions = [...questions];
        updatedQuestions[existingQuestionIndex] = { id, question, answers, correctAnswerIndex };
        setQuestions(updatedQuestions);
    };

    const addQuestion = (newQuestion) => {
        setQuestions(prevQuestions => [...prevQuestions, newQuestion]);
    };

    /**
     * Получает вопрос и его варианты ответов по уникальному идентификатору.
     * @param {number} id - Уникальный идентификатор вопроса.
     * @returns {Object|undefined} - Возвращает объект вопроса или undefined, если вопрос не найден.
     */
    const getQuestion = (id) => {
        return questions.find(q => q.id === id);
    };


    const deleteQuestion = (id) => {
        setQuestions(prevQuestions => {
            // Фильтруем вопросы, удаляя тот, который соответствует данному id
            const updatedQuestions = prevQuestions.filter(q => q.id !== id);

            // Обновляем идентификаторы оставшихся вопросов
            return updatedQuestions.map((q, index) => ({
                ...q,
                id: index + 1 // Присваиваем новый идентификатор на основе индекса
            }));
        });
    };


    return (
        <QuestionContext.Provider value={{questions, updateQuestion, getQuestion, deleteQuestion, addQuestion}}>
            {children}
        </QuestionContext.Provider>
    );
};
