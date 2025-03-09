import axios from 'axios';

const BASE_URL = import.meta.env.VITE_API_BASE_URL;

export const sendQuestions = async (questions) => {
    try {
        console.log("Я в sendQuestions");
        console.log("Полученные вопросы:", questions);

        const response = await axios.post(`${BASE_URL}/api/questions`, questions);
        return response.data;
    } catch (error) {
        console.error('Ошибка при отправке вопросов:', error);
        throw error.response;
    }
};
