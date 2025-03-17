import { useContext, useEffect, useState, useRef } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { QuestionContext } from '../context/QuestionContext';
import "./creatorQuestion.css";

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
    }, [questions.length]);

    useEffect(() => {
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            setQuestion(currentQuestion.question);
            setAnswers(currentQuestion.answers);
            setCorrectAnswerIndex(currentQuestion.correctAnswerIndex);
        } else {
            setQuestion('');
            setAnswers(['', '', '', '']);
            setCorrectAnswerIndex(null);
        }
    }, [questions, id]);

    const handleQuestionChange = (value) => {
        setQuestion(value);
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updateQuestion(currentQuestion.id, value, answers, correctAnswerIndex);
        }
    };

    const handleAnswerChange = (index, value) => {
        const newAnswers = [...answers];
        newAnswers[index] = value;
        setAnswers(newAnswers);
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updateQuestion(currentQuestion.id, question, newAnswers, correctAnswerIndex);
        }
    };

    const handleCorrectAnswerIndexChange = (index) => {
        setCorrectAnswerIndex(index);
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updateQuestion(currentQuestion.id, question, answers, index);
        }
    };

    const addAnswerField = () => {
        if (answers.length < 6) {
            const newAnswers = [...answers, ''];
            setAnswers(newAnswers);
            const currentQuestion = getQuestion(parseInt(id));
            if (currentQuestion) {
                updateQuestion(currentQuestion.id, question, newAnswers, correctAnswerIndex);
            }
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
        const currentQuestion = getQuestion(parseInt(id));
        if (currentQuestion) {
            updateQuestion(currentQuestion.id, question, newAnswers, correctAnswerIndex);
        }
    };

    const handlePreviousQuestion = () => {
        if (parseInt(id) > 1) {
            navigate(`/createQuestion/${parseInt(id) - 1}`);
        } else {
            alert("Это первый вопрос.");
        }
    };

    const handleNextQuestion = () => {
        if (parseInt(id) < questions.length) {
            navigate(`/createQuestion/${parseInt(id) + 1}`);
        } else {
            alert("Это последний вопрос. Переход невозможен.");
        }
    };

    const handleDeleteQuestion = () => {
        const currentQuestion = getQuestion(parseInt(id));
        console.log(currentQuestion);
        if (currentQuestion) {
            deleteQuestion(currentQuestion.id);

            // Переход на предыдущий вопрос, если он существует
            const newId = parseInt(id) > 1 ? parseInt(id) - 1 : (questions.length > 1 ? 1 : null);

            if (newId) {
                navigate(`/createQuestion/${newId}`);
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

        setQuestion('');
        setAnswers(['', '', '', '']);
        setCorrectAnswerIndex(null);

        const newQuestionId = questions.length + 1;
        addQuestion({ id: newQuestionId, question: '', answers: ['', '', '', ''], correctAnswerIndex: null });

        navigate(`/createQuestion/${newQuestionId}`);
    };

    return (
        <div style={{ maxWidth: '500px', margin: '0 auto' }}>
            <div style={{ border: '1px solid #ccc', padding: '10px', borderRadius: '5px', marginBottom: '20px' }}>
                <div style={{ display: 'flex', alignItems: 'center', marginBottom: '10px' }}>
                    <span style={{ marginRight: '10px' }}>№ {parseInt(id)}:</span>
                    <input
                        type="text"
                        placeholder="Введите вопрос"
                        value={question}
                        onChange={(e) => handleQuestionChange(e.target.value)}
                        maxLength={maxQuestionLength}
                        style={{ flex: 1, border: '1px solid #ccc', padding: '5px' }}
                    />
                </div>
                {question.length >= maxQuestionLength && (
                    <div style={{ color: 'red', marginTop: '5px', marginBottom: '10px' }}>
                        Достигнут максимум символов ({maxQuestionLength} символов).
                    </div>
                )}
                <div>
                    <div
                        style={{
                            ...(answers.length > 4
                                ? { maxHeight: '240px', overflowY: 'auto', marginBottom: '10px' } // Увеличиваем maxHeight, чтобы вместить 5-й и 6-й ответы
                                : { minHeight: '240px', marginBottom: '10px' }), // Фиксируем minHeight для случаев <= 4 ответов
                        }}
                    >
                        {answers.map((answer, index) => (
                            <div key={index} style={{ marginBottom: '10px' }}>
                                <div style={{ display: 'flex', alignItems: 'center' }}>
                                    <input
                                        type="radio"
                                        name="answer"
                                        checked={correctAnswerIndex === index}
                                        onChange={() => handleCorrectAnswerIndexChange(index)}
                                        style={{ marginRight: '10px' }}
                                    />
                                    <input
                                        type="text"
                                        placeholder={`Вариант ответа ${index + 1}`}
                                        value={answer}
                                        onChange={(e) => handleAnswerChange(index, e.target.value)}
                                        maxLength={maxAnswerLength}
                                        style={{ flex: 1, border: '1px solid #ccc', padding: '5px', marginRight: '10px' }}
                                    />
                                    <button onClick={() => removeAnswerField(index)} style={{ padding: '5px' }}>
                                        -
                                    </button>
                                </div>
                                {answer.length >= maxAnswerLength && (
                                    <div style={{ color: 'red', marginTop: '5px' }}>
                                        Достигнут максимум символов ({maxAnswerLength} символов).
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                    <div style={{ height: '30px' }}> {/* Фиксированный контейнер для кнопки */}
                        {answers.length < 6 && (
                            <button onClick={addAnswerField} style={{ padding: '5px' }}>
                                +
                            </button>
                        )}
                    </div>
                </div>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <button className="btn btn-block" onClick={handlePreviousQuestion}>Предыдущий вопрос</button>
                <button className="btn btn-block" onClick={handleNextQuestion}>Следующий вопрос</button>
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '10px' }}>
                <button className="btn" onClick={handleDeleteQuestion}
                        style={{ visibility: questions.length > 1 ? 'visible' : 'hidden' }}>
                    Удалить вопрос
                </button>
                <button className="btn" onClick={handleAddNewQuestion}
                        style={{ visibility: (questions.length === parseInt(id)) ? 'visible' : 'hidden' }}>
                    Добавить вопрос
                </button>
            </div>
        </div>
    );
}
