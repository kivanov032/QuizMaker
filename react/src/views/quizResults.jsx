import {Link, useNavigate} from "react-router-dom";
import { useEffect, useState } from "react";
import { useQuizContext } from "../context/QuizContext";
import { checkQuizAnswers } from "../SenderQuizPassing.js";
import "./QuizResults.css";
import {useStateContext} from "../context/ContextProvider.jsx";

export default function QuizResults() {
    const navigate = useNavigate();
    const { quiz, quizQuestions, userAnswers} = useQuizContext();
    const { user} = useStateContext(); //Состояние для токена
    const [results, setResults] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchQuizResults = async () => {
            if (!quizQuestions.length) {
                setError("Недостаточно данных для получения результатов");
                return;
            }

            setLoading(true);
            setError(null);

            try {
                const { status, data } = await checkQuizAnswers(userAnswers, quizQuestions, user.login);

                if (status === 200) {
                    setResults(data);
                    console.log("data: ", data)
                    console.log("quizQuestions", quizQuestions)
                } else {
                    handleResultError(status);
                }
            } catch (err) {
                console.error('Ошибка при получении результатов:', err);
                setError(
                    err.response?.data?.message ||
                    "Сервер недоступен. Попробуйте позже."
                );
            } finally {
                setLoading(false);
            }
        };

        const handleResultError = (status) => {
            const errorMessages = {
                422: "Некорректные данные запроса",
                500: "Ошибка сервера при обработке результатов"
            };
            setError(errorMessages[status] || "Неизвестная ошибка");
        };

        fetchQuizResults();
    }, [quiz?.id_quiz, quizQuestions, userAnswers]);

    if (!quiz || !quizQuestions.length) {
        return (
            <div className="error-message">
                <p>Результаты не найдены. Пройдите викторину сначала.</p>
                <Link to="/">Вернуться к викторинам</Link>
            </div>
        );
    }

    const handleHomeButtonClick = () => {
        navigate(`/`);
    };

    return (
        <div className="quiz-results-container">
            <div className="quiz-results-box">
                {/*<h2 className="quiz-results-title" >*/}
                {/*    Результаты по викторине: {quiz?.name_quiz || 'Безымянная'}*/}
                {/*</h2>*/}

                <h2 className="quiz-results-title" style={{ color: '#06063e' }}>
                    Результаты по викторине: {quiz?.name_quiz || 'Безымянная'}
                </h2>

                {error && (
                    <div className="alert">
                        {Object.keys(error).map(key => (
                            <p key={key}>{error[key][0]}</p>
                        ))}
                    </div>
                )}

                {loading && <div className="loading-text show">Загрузка...</div>}

                {!loading && results && (
                    <>
                        <div className="score-display">
                            <span className="score">
                                {results.summary.correctAnswers}
                            </span>
                            <span className="divider">/</span>
                            <span className="total">
                                {results.summary.totalQuestions}
                            </span>
                            <span className="percentage">
                                ({results.summary.scorePercentage}%)
                            </span>
                        </div>

                        <div className="summary-stats">
                            <div className="stat-item">
                                <span>Правильно: </span>
                                <span className="stat-value correct">{results.summary.correctAnswers}</span>
                            </div>
                            <div className="stat-item">
                                <span>Неправильно: </span>
                                <span className="stat-value incorrect">{results.summary.wrongAnswers}</span>
                            </div>
                            <div className="stat-item">
                                <span>Пропущено: </span>
                                <span className="stat-value skipped">{results.summary.skippedQuestions}</span>
                            </div>
                        </div>

                        <div className="results-details">
                            <h3>Детализация ответов:</h3>
                            {Object.entries(results.results).map(([questionId, detail], index) => (
                                <div
                                    key={questionId}
                                    className={`detail-item ${detail.status === 'Right' ? 'correct' : 'incorrect'}`}
                                >
                                    <div className="question-text">
                                        Вопрос {index + 1}: {quizQuestions[index].question || 'Текст вопроса не найден'}
                                    </div>
                                    <div className="user-answer">
                                        {detail.status === 'Not Entered'
                                            ? "Нет ответа"
                                            : `Ваш ответ: ${detail.userAnswer}`
                                        }
                                    </div>
                                    {detail.status !== 'Right' && (
                                        <div className="correct-answer">
                                            Правильный ответ: {detail.correctAnswer}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>


                        <div className="navigation-buttons" style={{
                            display: 'flex',
                            justifyContent: 'center',
                            margin: '30px 0'
                        }}>
                            <button
                                onClick={handleHomeButtonClick}
                                className="next-btn"
                                style={{
                                    padding: '12px 40px',
                                    fontSize: '16px',
                                    borderRadius: '6px'
                                }}
                            >
                                На главную страницу
                            </button>
                        </div>

                    </>
                )}
            </div>
        </div>
    );
}
