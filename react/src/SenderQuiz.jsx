import axios from 'axios';

const BASE_URL = import.meta.env.VITE_API_BASE_URL;

/**
 * Отправляет вопросы для поиска ошибок в викторине.
 * @param {string} quizName - Название викторины.
 * @param {Array} questions - Список вопросов.
 * @returns {Promise} - Ответ от сервера.
 */
export const sendQuestionsToSearchError = async (quizName, questions) => {
    try {
        console.log('Отправка запроса для поиска ошибок в викторине:', { quizName, questions });

        const response = await axios.post(`${BASE_URL}/api/searchQuizErrors`, {
            questions,
            quizName,
        });

        return response.data;
    } catch (error) {
        console.error('Ошибка при отправке запроса для поиска ошибок:', error);
        throw error.response;
    }
};

/**
 * Отправляет вопросы для исправления ошибок в викторине с последующим поиском новых ошибок в викторине.
 * @param {string} quizName - Название викторины.
 * @param {Array} questions - Список вопросов.
 * @param {Array} errors - Список ошибок для исправления.
 * @returns {Promise} - Обработанные данные от сервера.
 */
export const sendQuestionsToFixError = async (quizName, questions, errors) => {
    try {
        console.log('Отправка запроса для исправления ошибок в викторине:', { quizName, questions, errors });

        const response = await axios.post(`${BASE_URL}/api/fixQuizErrors`, {
            questions,
            quizName,
            errors,
            searchQuizErrors_flag: true,
        });

        console.log('Ответ от сервера:', response.data);

        // Обработка данных для замены null на пустые строки
        const processedData = {
            ...response.data,
            quizName: response.data.quizName ?? '',
            questions: response.data.questions.map(question => ({
                ...question,
                question: question.question ?? '',
                answers: question.answers.map(answer => answer ?? ''),
            })),
        };

        console.log('Обработанные данные:', processedData);
        return processedData;
    } catch (error) {
        console.error('Ошибка при отправке запроса для исправления ошибок:', error);
        throw error.response;
    }
};

/**
 * Отправляет вопросы для записи в базу данных (с предварительной обработкой ошибок)
 * @param {string} quizName - Название викторины.
 * @param {Array} questions - Список вопросов.
 * @param {{cosmeticErrorQuizName: boolean, cosmeticErrors: boolean, minorErrors: boolean, logicalErrors: boolean}} errors - Список ошибок.
 * @returns {Promise} - Ответ от сервера.
 */
export const sendQuestionsToRecordInBD = async (quizName, questions, errors) => {
    try {
        console.log('Отправка запроса для записи вопросов в БД:', { quizName, questions, errors });

        const response = await axios.post(`${BASE_URL}/api/createQuizWithQuestions`, {
            questions,
            quizName,
            errors,
        });

        console.log('Ответ от сервера:', response.data);
        return response.data;
    } catch (error) {
        console.error('Ошибка при отправке запроса для записи вопросов в БД:', error);
        throw error.response;
    }
};

/**
 * Проверяет соединение с базой данных.
 * @returns {Promise} - Результат проверки соединения.
 */
export const checkConnectionWithBD = async () => {
    try {
        console.log('Проверка соединения с БД...');

        const response = await axios.get(`${BASE_URL}/api/checkConnectionWithBD`);

        console.log('Ответ от сервера:', response.data);

        if (response.data.status === 'success') {
            console.log('Соединение с БД успешно установлено.');
            return response.data;
        } else {
            const errorMessage = response.data.message || 'Ошибка при проверке соединения с БД';
            console.error(errorMessage);
            return { error: errorMessage };
        }
    } catch (error) {
        console.error('Ошибка при проверке соединения с сервером:', error);
        return { error: error.response || error };
    }
};
