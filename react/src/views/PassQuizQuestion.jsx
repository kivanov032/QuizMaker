import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useQuizContext } from '../context/QuizContext';
<<<<<<< HEAD
import "./PassQuizQuestion.css";
=======
import './passQuizQuestion.css';
>>>>>>> markast

export default function PassQuizQuestion() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { quizQuestions, userAnswers, setUserAnswers } = useQuizContext();

    const [currentQuestion, setCurrentQuestion] = useState(null);
    const [questionNumber, setQuestionNumber] = useState(0);

    const selectedAnswer = userAnswers[questionNumber] ?? null;

    useEffect(() => {
        if (!id || !quizQuestions?.length) return;

        const questionId = parseInt(id) || 1;
        const validQuestionId = Math.max(1, Math.min(questionId, quizQuestions.length));

        if (questionId !== validQuestionId) {
            navigate(`/passQuiz/${validQuestionId}`, { replace: true });
            return;
        }

        const newQuestion = quizQuestions[validQuestionId - 1] || null;
        setCurrentQuestion(newQuestion);
        setQuestionNumber(validQuestionId);
    }, [id, quizQuestions, navigate]);

    const handleAnswerSelect = (index) => {
        setUserAnswers((prev) => ({
            ...prev,
            [questionNumber]: index,
        }));
    };

    const handleNextQuestion = () => {
        const nextQuestion = questionNumber + 1;
        if (nextQuestion <= quizQuestions.length) {
            navigate(`/passQuiz/${nextQuestion}`);
        } else {
            console.log('userAnswers:', userAnswers);
            console.log('АААААААААААААААА!!!!!!!!!!1');
            navigate('/passQuiz/quizResult');
        }
    };

    const handlePreviousQuestion = () => {
        const prevQuestion = questionNumber - 1;
        if (prevQuestion >= 1) {
            navigate(`/passQuiz/${prevQuestion}`);
        }
    };

    if (!quizQuestions?.length) {
        return <div className="loading-message">Загрузка вопросов...</div>;
    }

    if (!currentQuestion) {
        return <div className="error-message">Вопрос не найден</div>;
    }

    return (
        <div className="quiz-question-container">
            <div className="question-header">
                <h3>
                    Вопрос {questionNumber} из {quizQuestions.length}
                </h3>
            </div>

            <div className="question-text-wrapper">
                <div className="question-text">{currentQuestion.question}</div>
            </div>

            <div className="answers-list">
                {currentQuestion.answers.map((answer, index) => (
                    <div
                        key={`${id}_${index}`}
                        className={`answer-option ${selectedAnswer === index ? 'selected' : ''}`}
                        onClick={() => handleAnswerSelect(index)}
                    >
                        <div className="answer-radio">
                            <input
                                type="radio"
                                name="answer"
                                checked={selectedAnswer === index}
                                onChange={() => {}}
                            />
                        </div>
                        <div className="answer-text">{answer}</div>
                    </div>
                ))}
            </div>

            <div className="navigation-buttons">
                <button
                    onClick={handlePreviousQuestion}
                    disabled={questionNumber <= 1}
                    className="prev-btn"
                >
                    Предыдущий вопрос
                </button>
                <button onClick={handleNextQuestion} className="next-btn">
                    {questionNumber < quizQuestions.length ? 'Следующий вопрос' : 'Завершить'}
                </button>
            </div>
        </div>
    );
}
