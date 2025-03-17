import axios from 'axios';

const BASE_URL = import.meta.env.VITE_API_BASE_URL;

export const sendQuestionsToSearchError = async (quizName, questions) => {
    try {
        console.log("Я в sendQuestionsToSearchError");
        console.log("Вопросы:", questions);
        console.log("Название викторины:", quizName);

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

export const sendQuestionsToFixError = async (quizName, questions, errors, create_quiz_flag) => {
    try {
        console.log("Я в sendQuestionsToFixError");
        console.log("Вопросы:", questions);
        console.log("Название викторины:", quizName);
        console.log("Метки на исправление ошибок:", errors);

        const response = await axios.post(`${BASE_URL}/api/fixQuizErrors`, {
            questions: questions,
            quizName: quizName,
            errors: errors,
            create_quiz_flag: create_quiz_flag
        });

        console.log("Полученный ответ от сервера:", response.data);

        // Преобразование данных после получения ответа от сервера
        const processedData = {
            ...response.data,
            quizName: response.data.quizName ?? "", // Заменяем null на пустую строку для quizName
            questions: response.data.questions.map(question => ({
                ...question,
                question: question.question ?? "", // Заменяем null на пустую строку для текста вопроса
                answers: question.answers.map(answer => answer ?? ""), // Заменяем null на пустую строку для ответов
            })),
        };

        console.log("Обработанные данные:", processedData);
        return processedData;
    } catch (error) {
        console.error('Ошибка при отправке вопросов:', error);
        throw error.response;
    }
};


