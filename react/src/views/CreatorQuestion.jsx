import React, { useContext, useEffect, useState, useRef } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { QuestionContext } from '../context/QuestionContext';
import { sendQuestions } from "../senderQuiz.jsx";

export default function CreatorQuestion() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { questions = [], updateQuestion, getQuestion, deleteQuestion, addQuestion } = useContext(QuestionContext);

    const [question, setQuestion] = useState('');
    const [answers, setAnswers] = useState(['', '', '', '']);
    const [correctAnswerIndex, setCorrectAnswerIndex] = useState(null);
    const [questionsCount, setQuestionsCount] = useState(0);
    const isFirstRender = useRef(true);

    const maxQuestionLength = 350;
    const maxAnswerLength = 150;



    useEffect(() => {
        if (isFirstRender.current) {
            if (questions.length === 0) {
                addQuestion({ question: null, answers: [null, null, null, null], correctAnswerIndex: null });
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


    useEffect(() => {
        setQuestionsCount(questions.length);
    }, [questions]);

    /**
     * Обработка изменений текста вопроса.
     * @param {string} value - Новое значение вопроса.
     */
    const handleQuestionChange = (value) => {
        setQuestion(value);
    };


    /**
     * Обработка изменений текста ответа.
     * @param {number} index - Индекс ответа.
     * @param {string} value - Новое значение ответа.
     */
    const handleAnswerChange = (index, value) => {
        const newAnswers = [...answers];
        newAnswers[index] = value;
        setAnswers(newAnswers);
    };


    /**
     * Установка индекса правильного ответа.
     * @param {number} index - Индекс правильного ответа.
     */
    const handleCorrectAnswerChange = (index) => {
        setCorrectAnswerIndex(index);
    };


    /**
     * Добавление нового поля для ответа, если их меньше 6.
     */
    const addAnswerField = () => {
        if (answers.length < 6) {
            setAnswers([...answers, '']);
        }
    };


    /**
     * Удаление поля для ответа по его индексу.
     * @param {number} index - Индекс ответа для удаления.
     */
    const removeAnswerField = (index) => {
        const newAnswers = answers.filter((_, i) => i !== index);
        if (correctAnswerIndex === index) {
            setCorrectAnswerIndex(null);
        } else if (correctAnswerIndex > index) {
            setCorrectAnswerIndex(correctAnswerIndex - 1);
        }
        setAnswers(newAnswers);
    };


    /**
     * Обработка перехода к предыдущему вопросу с
     * обновлением текущего вопроса перед переходом.
     */
    const handlePreviousQuestion = () => {
        updateQuestion(parseInt(id), question, answers, correctAnswerIndex);
        if (parseInt(id) > 1) {
            navigate(`/createQuestion/${parseInt(id) - 1}`);
        }
    };


    /**
     * Обработка перехода к следующему вопросу (если он уже существует, иначе предупреждение об ошибке) с
     * обновлением текущего вопроса перед переходом.
     */
    const handleNextQuestion = () => {
        updateQuestion(parseInt(id), question, answers, correctAnswerIndex);
        const updatedQuestionsCount = questions.length;

        // Проверка на существование следующего вопроса
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

        deleteQuestion(parseInt(id));

        console.log(questions);

        // Проверка, что questions не равен null
        if (!questions) {
            console.error("Ошибка: questions равен null");
            return;
        }

        // Переход на предыдущий вопрос, если он существует
        if (parseInt(id) > 0) {
            navigate(`/createQuestion/${parseInt(id) - 1}`);
        } else if (questions.length > 1) {
            navigate(`/createQuestion/2`);
        } else {
            navigate('/someOtherPage');
        }
    };




    const handleAddNewQuestion = () => {
        updateQuestion(parseInt(id), question, answers, correctAnswerIndex);
        const newQuestionId = questionsCount + 1;
        addQuestion({ question: null, answers: [null, null, null, null], correctAnswerIndex: null });
        navigate(`/createQuestion/${newQuestionId}`);
    };


    const handleFinishButtonClick = async () => {
        const updatedQuestions = [...questions];
        updatedQuestions[parseInt(id) - 1] = {
            question,
            answers,
            correctAnswerIndex
        };

        try {
            await sendQuestions(updatedQuestions);
        } catch (error) {
            console.error('Ошибка:', error);
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
                    <div key={index} style={{ display: 'flex', alignItems: 'center', marginBottom: '10px' }}>
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
                        {answer.length >= maxAnswerLength && (
                            <div style={{ color: 'red', marginTop: '5px' }}>
                                Достигнут максимум символов ({maxAnswerLength} символов).
                            </div>
                        )}
                        <button onClick={() => removeAnswerField(index)}>-</button>
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
            {(parseInt(id) === questionsCount) && (
                <button className="btn btn-primary" onClick={handleAddNewQuestion} style={{ marginTop: '20px' }}>
                    + Вопрос
                </button>
            )}
        </div>
    );
}
