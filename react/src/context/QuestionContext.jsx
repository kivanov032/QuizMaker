import { createContext, useState } from 'react';

// Контекст для вопросов
export const QuestionContext = createContext();

// Провайдер для управления состоянием вопросов
export const QuestionProvider = ({ children }) => {
    const [questions, setQuestions] = useState([]);

    /**
     * Обновляет существующий вопрос или добавляет новый.
     * @param {number} id - Уникальный идентификатор вопроса.
     * @param {string} question - Текст вопроса.
     * @param {Array<string>} answers - Массив возможных ответов.
     * @param {number} correctAnswerIndex - Индекс правильного ответа в массиве ответов.
     */
    const updateQuestion = (id, question, answers, correctAnswerIndex) => {
        const existingQuestionIndex = questions.findIndex(q => q.id === id);

        if (existingQuestionIndex !== -1) {
            const updatedQuestions = [...questions];
            updatedQuestions[existingQuestionIndex] = { id, question, answers, correctAnswerIndex };
            setQuestions(updatedQuestions);
        } else {
            setQuestions([...questions, { id, question, answers, correctAnswerIndex }]);
        }
    };

    /**
     * Получает вопрос и его варианты ответов по уникальному идентификатору.
     * @param {number} id - Уникальный идентификатор вопроса.
     * @returns {Object|undefined} - Возвращает объект вопроса или undefined, если вопрос не найден.
     */
    const getQuestion = (id) => {
        return questions.find(q => q.id === id);
    };

    /**
     * Удаляет вопрос по его уникальному идентификатору.
     * @param {number} id - Уникальный идентификатор вопроса.
     */
    const deleteQuestion = (id) => {
        setQuestions(questions.filter(q => q.id !== id).map((q, index) => ({
            ...q,
            id: index + 1 // Индексы обновляются после удаления
        })));
    };

    return (
        <QuestionContext.Provider value={{ questions, updateQuestion, getQuestion, deleteQuestion }}>
            {children}
        </QuestionContext.Provider>
    );
};
