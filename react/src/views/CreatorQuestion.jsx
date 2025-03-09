import React, { useContext, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { QuestionContext } from '../context/QuestionContext';
import { sendQuestions } from "../senderQuiz.jsx";

export default function CreatorQuestion() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { questions, updateQuestion, getQuestion, deleteQuestion } = useContext(QuestionContext);

    const [question, setQuestion] = useState('');
    const [answers, setAnswers] = useState(['', '', '', '']);
    const [correctAnswerIndex, setCorrectAnswerIndex] = useState(null);

    useEffect(() => {
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            setQuestion(currentQuestion.question);
            setAnswers(currentQuestion.answers || ['', '', '', '']);
            setCorrectAnswerIndex(currentQuestion.correctAnswerIndex !== undefined ? currentQuestion.correctAnswerIndex : null);
        } else {
            setQuestion('');
            setAnswers(['', '', '', '']);
            setCorrectAnswerIndex(null);
        }
    }, [id, getQuestion]);

    /**
     * Обрабатывает изменение текста вопроса.
     * @param {Object} e - Событие изменения.
     */
    const handleQuestionChange = (e) => {
        setQuestion(e.target.value);
    };

    /**
     * Обрабатывает изменение текста ответа.
     * @param {number} index - Индекс ответа.
     * @param {string} value - Новое значение ответа.
     */
    const handleAnswerChange = (index, value) => {
        const newAnswers = [...answers];
        newAnswers[index] = value;
        setAnswers(newAnswers);
    };

    /**
     * Устанавливает индекс правильного ответа.
     * @param {number} index - Индекс правильного ответа.
     */
    const handleCorrectAnswerChange = (index) => {
        setCorrectAnswerIndex(index);
    };

    /**
     * Добавляет новое поле для ответа, если их меньше 6.
     */
    const addAnswerField = () => {
        if (answers.length < 6) {
            setAnswers([...answers, '']);
        }
    };

    /**
     * Удаляет поле для ответа по его индексу.
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
     * Обрабатывает переход к предыдущему вопросу.
     * Обновляет текущий вопрос перед переходом.
     */
    const handlePreviousQuestion = () => {
        updateQuestion(parseInt(id), question, answers, correctAnswerIndex);
        if (parseInt(id) > 1) {
            navigate(`/createQuestion/${parseInt(id) - 1}`);
        }
    };

    /**
     * Обрабатывает переход к следующему вопросу.
     * Обновляет текущий вопрос перед переходом.
     */
    const handleNextQuestion = () => {
        updateQuestion(parseInt(id), question, answers, correctAnswerIndex);
        navigate(`/createQuestion/${parseInt(id) + 1}`);
    };

    /**
     * Обрабатывает удаление текущего вопроса.
     * Переходит к предыдущему вопросу после удаления.
     */
    const handleDeleteQuestion = () => {
        deleteQuestion(parseInt(id));
        if (parseInt(id) > 1) {
            navigate(`/createQuestion/${parseInt(id) - 1}`);
        }
    };

    /**
     * Обрабатывает нажатие кнопки завершения.
     * Отправляет обновленные вопросы на отправитель данных на сервер.
     */
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
            <h1 style={{ marginBottom: '10px' }}>Create Question:</h1>
            <div style={{ border: '1px solid #ccc', padding: '10px', borderRadius: '5px', marginBottom: '20px' }}>
                <div style={{ display: 'flex', alignItems: 'center', marginBottom: '10px' }}>
                    <span style={{ marginRight: '10px' }}>№ {parseInt(id)}:</span>
                    <input
                        type="text"
                        placeholder="Введите вопрос"
                        value={question}
                        onChange={handleQuestionChange}
                        style={{ flex: 1 }}
                    />
                </div>
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
                            style={{ flex: 1, marginRight: '10px' }}
                        />
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
        </div>
    );
}
