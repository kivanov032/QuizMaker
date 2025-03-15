import axios from 'axios';

const BASE_URL = import.meta.env.VITE_API_BASE_URL;

export const sendQuestions = async (questions) => {
    try {
        console.log("Я в sendQuestions");
        console.log("Полученные вопросы:", questions);

        const quizName = "  \nПрограммирование \n   на PHP \t ";

        const response = await axios.post(`${BASE_URL}/api/searchQuizErrors`, {
            questions: questions,
            quizName: quizName
        });

        console.log("Полученный ответ от сервера:", response.data);
        return response.data;
    } catch (error) {
        console.error('Ошибка при отправке вопросов:', error);
        throw error.response;
    }
};
