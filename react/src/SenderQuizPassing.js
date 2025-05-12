import axios from 'axios';

const BASE_URL = import.meta.env.VITE_QUIZ_API_BASE_URL;

/**
 * Поиск викторин по названию (регистронезависимый).
 * @param {string} quizName - Название викторины.
 * @returns {Promise<{status: number, data: any}>} - Ответ сервера с полной информацией (статус + данные).
 */
export const searchQuiz = async (quizName) => {
    try {
        const response = await axios.post(`${BASE_URL}/api/search-quiz`, {
            quizName,
        });

        return {
            status: response.status,
            data: response.data,
        };
    } catch (error) {
        if (error.response) {
            return {
                status: error.response.status,
                data: error.response.data,
            };
        } else {
            throw new Error('Сервер недоступен. Проверьте подключение к интернету.');
        }
    }
};

/**
 * Поиск вопросов по названию ID викторины.
 * @param {string} id_quiz - ID викторины.
 * @returns {Promise<{status: number, data: any}>} - Ответ сервера с полной информацией (статус + данные).
 */
export const downloadQuizQuestion = async (id_quiz) => {
    try {
<<<<<<< HEAD
        const response = await axios.post(`${BASE_URL}/api/get-quiz-questions`, {
            id_quiz
=======
        const response = await axios.post(`${BASE_URL}/api/download-quiz-questions`, {
            id_quiz,
>>>>>>> markast
        });

        return {
            status: response.status,
            data: response.data,
        };
    } catch (error) {
        if (error.response) {
            return {
                status: error.response.status,
                data: error.response.data,
            };
        } else {
            throw new Error('Сервер недоступен. Проверьте подключение к интернету.');
        }
    }
};

/**
 * Проверка ответов пользователя на вопросы викторины.
 * @param {{}} userAnswers - Массив ответов пользователя
 *     (структура: {id_question: идентификатор вопроса, answer: выбранный ответ}).
 * @param {Array<{id_question: string, correctAnswer: string}>} quizQuestions - Массив вопросов викторины
 *     (структура: {id_question: идентификатор вопроса, correctAnswer: верный ответ}).
 * @param login
 * @returns {Promise<{status: number, data: any}>} - Ответ сервера с результатами проверки:
 *     - status: HTTP-статус код (200 при успехе)
 *     - data: {
 *         score: number,            // Количество правильных ответов
 *         totalQuestions: number,   // Общее количество вопросов
 *         details: Array<{          // Детализация по каждому вопросу
 *             id_question: string,
 *             isCorrect: boolean,
 *             userAnswer: string,
 *             correctAnswer: string
 *         }>
 *       } | { errors: object }      // В случае ошибки валидации
 * @throws {Error} - При отсутствии подключения к серверу (без response в error)
 */
export const checkQuizAnswers = async (userAnswers, quizQuestions, login) => {
    try {
        const response = await axios.post(`${BASE_URL}/api/check-quiz-answers`, {
            userAnswers,
            quizQuestions,
<<<<<<< HEAD
            login
=======
>>>>>>> markast
        });

        return {
            status: response.status,
            data: response.data,
        };
    } catch (error) {
        if (error.response) {
            return {
                status: error.response.status,
                data: error.response.data,
            };
        } else {
            throw new Error('Сервер недоступен. Проверьте подключение к интернету.');
        }
    }
};
