import { Outlet, useNavigate } from 'react-router-dom';
import { useQuizContext } from '../context/QuizContext.jsx';
import { downloadQuizQuestion } from '../SenderQuizPassing.js';
import { useEffect, useState } from 'react';

export default function PassQuizLayout() {
    const navigate = useNavigate();
    const { quiz, setQuizQuestions, resetUserAnswers } = useQuizContext();
    const [error, setError] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    // Загрузка вопросов викторины
    useEffect(() => {
        const fetchQuizQuestions = async () => {
            if (!quiz?.id_quiz) return;

            setIsLoading(true);
            setError(null);

            try {
                const { status, data } = await downloadQuizQuestion(quiz.id_quiz);

                if (status === 200) {
                    setQuizQuestions(data.questions);
                    resetUserAnswers();
                    console.log(data.questions);
                    console.log('Я тууууууут!!!!!!!!!!!!1111');
                    navigate(`/passQuiz/1`);
                } else {
                    handleResultError(status);
                }
            } catch (err) {
                console.error('Ошибка загрузки:', err);
                setError('Сервер недоступен. Попробуйте позже.');
            } finally {
                setIsLoading(false);
            }
        };

        // Вспомогательная функция для обработки специфичных ошибок
        const handleResultError = (status) => {
            const errorMessages = {
                404: 'Викторина не найдена',
                422: 'Некорректный запрос',
                500: 'Ошибка сервера при обработке результатов',
            };
            setError(errorMessages[status] || 'Неизвестная ошибка');
        };

        fetchQuizQuestions();
    }, [navigate, quiz.id_quiz, setQuizQuestions]);

    if (isLoading) {
        return <div className="loading-frame">Загрузка вопросов...</div>;
    }

    if (error) {
        return (
            <div className="error-frame">
                <h3>Ошибка</h3>
                <p>{error}</p>
                <button onClick={() => window.location.reload()}>Попробовать снова</button>
            </div>
        );
    }

    return <Outlet />;
}
