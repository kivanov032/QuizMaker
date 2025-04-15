import axios from 'axios';

const BASE_URL = import.meta.env.VITE_QUIZ_API_BASE_URL;

/**
 * Отправляет вопрос на поиск викторин с данным именем (поиск по тегу, регистронезависимый).
 * @param {string} quizName - Название викторины.
 * @returns {Promise} - Обработанные данные от сервера.
 */
export const searchQuiz = async (quizName) => {
    try {
        console.log('Отправка запроса для поиска викторин:', { quizName});

        const response = await axios.post(`${BASE_URL}/api/`, {
            quizName
        });

        console.log('Ответ от сервера:', response.data);

        return response.data;
    } catch (error) {
        console.error('Ошибка при отправке запроса для поиска викторин:', error);
        throw error.response;
    }

};

