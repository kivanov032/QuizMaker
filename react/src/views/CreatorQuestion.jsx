import React, { useContext, useEffect, useState, useRef } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { QuestionContext } from '../context/QuestionContext';
import { sendQuestions } from "../senderQuiz.jsx";

export default function CreatorQuestion() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { questions, updateQuestion, getQuestion, deleteQuestion, addQuestion } = useContext(QuestionContext);

    const [question, setQuestion] = useState('');
    const [answers, setAnswers] = useState(['', '', '', '']);
    const [correctAnswerIndex, setCorrectAnswerIndex] = useState(null);
    const isFirstRender = useRef(true);

    const maxQuestionLength = 350;
    const maxAnswerLength = 150;

    useEffect(() => {
        if (isFirstRender.current) {
            if (questions.length === 0) {
                addQuestion({ id: 1, question: '', answers: ['', '', '', ''], correctAnswerIndex: null });
            }
            isFirstRender.current = false;
        }
    }, [questions.length, addQuestion]);

    useEffect(() => {
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            setQuestion(currentQuestion.question || '');
            setAnswers(currentQuestion.answers || ['', '', '', '']);
            setCorrectAnswerIndex(currentQuestion.correctAnswerIndex !== undefined ? currentQuestion.correctAnswerIndex : null);
        } else {
            setQuestion('');
            setAnswers(['', '', '', '']);
            setCorrectAnswerIndex(null);
        }
    }, [id, getQuestion]);


    const handleQuestionChange = (value) => {
        setQuestion(value);
    };

    const handleAnswerChange = (index, value) => {
        const newAnswers = [...answers];
        newAnswers[index] = value;
        setAnswers(newAnswers);
    };

    const handleCorrectAnswerChange = (index) => {
        setCorrectAnswerIndex(index);
    };

    const addAnswerField = () => {
        if (answers.length < 6) {
            setAnswers([...answers, '']);
        }
    };

    const removeAnswerField = (index) => {
        const newAnswers = answers.filter((_, i) => i !== index);
        if (correctAnswerIndex === index) {
            setCorrectAnswerIndex(null);
        } else if (correctAnswerIndex > index) {
            setCorrectAnswerIndex(correctAnswerIndex - 1);
        }
        setAnswers(newAnswers);
    };

    const handlePreviousQuestion = () => {
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updateQuestion(currentQuestion.id, question, answers, correctAnswerIndex);
        }
        if (parseInt(id) > 1) {
            navigate(`/createQuestion/${parseInt(id) - 1}`);
        }
    };

    const handleNextQuestion = () => {
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updateQuestion(currentQuestion.id, question, answers, correctAnswerIndex);
        }
        const updatedQuestionsCount = questions.length;
        if (parseInt(id) < updatedQuestionsCount) {
            navigate(`/createQuestion/${parseInt(id) + 1}`);
        } else {
            alert("Это последний вопрос. Переход невозможен.");
        }
    };

    const handleDeleteQuestion = () => {
        if (questions.length <= 1) {
            alert("Нельзя удалить единственный вопрос.");
            return;
        }

        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            deleteQuestion(currentQuestion.id);

            // Переход на предыдущий вопрос, если он существует
            if (parseInt(id) > 1) {
                navigate(`/createQuestion/${parseInt(id) - 1}`);
            } else if (questions.length > 1) {
                navigate(`/createQuestion/1`);
            } else {
                navigate('/someOtherPage');
            }
        }
    };

    const handleAddNewQuestion = () => {
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updateQuestion(currentQuestion.id, question, answers, correctAnswerIndex);
        }

        // Сброс локального состояния
        setQuestion('');
        setAnswers(['', '', '', '']);
        setCorrectAnswerIndex(null);

        // Добавление нового вопроса
        const newQuestionId = questions.length + 1;
        addQuestion({ id: newQuestionId, question: '', answers: ['', '', '', ''], correctAnswerIndex: null });

        // Переход на новую страницу
        navigate(`/createQuestion/${newQuestionId}`);
    };


    const handleFinishButtonClick = async () => {
        const updatedQuestions = [...questions];
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updatedQuestions[currentQuestion.id - 1] = {
                id: currentQuestion.id,
                question,
                answers,
                correctAnswerIndex
            };

            try {
                await sendQuestions(updatedQuestions);
            } catch (error) {
                console.error('Ошибка:', error);
            }
        }
    };


    return (
        <div style={{ maxWidth: '400px', margin: '0 auto' }}>
            <h1 style={{ marginBottom: '10px' }}>Создать вопрос:</h1>
            <div style={{ border: '1px solid #ccc', padding: '10px', borderRadius: '5px', marginBottom: '20px' }}>
                <div style={{ display: 'flex', alignItems: 'center', marginBottom: '10px' }}>
                    <span style={{ marginRight: '10px' }}>№ {parseInt(id)}:</span>
                    <input
                        type="text"
                        placeholder="Введите вопрос"
                        value={question}
                        onChange={(e) => handleQuestionChange(e.target.value)}
                        maxLength={maxQuestionLength}
                        style={{ flex: 1 }}
                    />
                </div>
                {question.length >= maxQuestionLength && (
                    <div style={{ color: 'red', marginTop: '5px' }}>
                        Достигнут максимум символов ({maxQuestionLength} символов).
                    </div>
                )}
                {answers.map((answer, index) => (
                    <div key={index} style={{ marginBottom: '10px' }}>
                        <div style={{ display: 'flex', alignItems: 'center' }}>
                            <input
                                type="radio"
                                name="answer"
                                checked={correctAnswerIndex === index}
                                onChange={() => handleCorrectAnswerChange(index)}
                                style={{ marginRight: '10px' }}
                            />
                            <input
                                type="text"
                                placeholder={`Вариант ответа ${index + 1}`}
                                value={answer}
                                onChange={(e) => handleAnswerChange(index, e.target.value)}
                                maxLength={maxAnswerLength}
                                style={{ flex: 1, marginRight: '10px' }}
                            />
                            <button onClick={() => removeAnswerField(index)}>-</button>
                        </div>
                        {answer.length >= maxAnswerLength && (
                            <div style={{ color: 'red', marginTop: '5px' }}>
                                Достигнут максимум символов ({maxAnswerLength} символов).
                            </div>
                        )}
                    </div>
                ))}
                {answers.length < 6 && (
                    <button onClick={addAnswerField} style={{ marginTop: '10px' }}>+</button>
                )}
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <button className="btn btn-block" onClick={handlePreviousQuestion}>Предыдущий вопрос</button>
                <button className="btn btn-block" onClick={handleNextQuestion}>Следующий вопрос</button>
            </div>
            <button className="btn btn-danger" onClick={handleDeleteQuestion} style={{ marginTop: '20px' }}>
                Удалить вопрос
            </button>
            <button className="btn btn-success" onClick={handleFinishButtonClick} style={{ marginTop: '20px' }}>
                Завершить
            </button>
            {(parseInt(id) === questions.length) && (
                <button className="btn btn-primary" onClick={handleAddNewQuestion} style={{ marginTop: '20px' }}>
                    + Вопрос
                </button>
            )}
        </div>
    );
}
