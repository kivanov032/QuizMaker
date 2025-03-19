import React, { useState, useContext, useEffect } from 'react';
import {Outlet, Link, Navigate, useNavigate} from "react-router-dom";
import { QuestionContext } from '../context/QuestionContext';
import {sendQuestionsToFixError, sendQuestionsToRecordInBD, sendQuestionsToSearchError} from "../SenderQuiz.jsx";
import "./CreatorLayout.css";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function CreatorLayout() {
    const navigate = useNavigate();
    const { questions, setQuestions  } = useContext(QuestionContext); //Контекст вопросов
    const [quizName, setQuizName] = useState(''); //Состояние для названия викторины
    const [quizErrors, setQuizErrors] = useState(null); //Состояние для отображения ошибок
    const { token } = useStateContext(); //Состояние для токена
    const maxNameQuizLength = 200; // Максимальная длина названия викторины

    //Состояние для чекбоксов ошибок (метки на то, какие ошибки исправить (все кроме критических))
    const [checkboxes, setCheckboxes] = useState({
        cosmeticErrorQuizName: false,
        cosmeticErrors: false,
        minorErrors: false,
        logicalErrors: false,
    });

    // Состояние для модального окна (всплывающее окно для окончательного завершения викторины)
    const [isModalOpen, setIsModalOpen] = useState(false);

    //Состояние для отображения ошибок (всех ошибок)
    const [expandedSections, setExpandedSections] = useState({
        quizErrors: quizErrors?.name_quiz_errors && Object.keys(quizErrors.name_quiz_errors).length > 0, // Раскрыть, если есть ошибки викторин
        questionErrors: (quizErrors?.critical_errors?.length > 0 || quizErrors?.cosmetic_errors?.length > 0 ||
            quizErrors?.logical_errors?.length > 0 || quizErrors?.minor_errors?.length > 0), // Раскрыть, если есть ошибки вопросов
        criticalQuizErrors: quizErrors?.name_quiz_errors?.critical_error, // Критические ошибки викторин раскрыты
        syntaxQuizErrors: false,
        criticalQuestionErrors: quizErrors?.critical_errors?.length > 0, // Критические ошибки вопросов раскрыты
        logicQuestionErrors: false,
        minorQuestionErrors: false,
        syntaxQuestionErrors: false,
    });

    if (!token) {
        return <Navigate to="/login" />; //Если нет токена, то переброс на регистрацию
    }

    // useEffect(() => {
    //     //console.log("quizErrors updated:", quizErrors);
    // }, [quizErrors]);

    //Хук для автоматического обновления состояния expandedSections при изменении quizErrors.
    useEffect(() => {
        if (quizErrors) {
            const hasCriticalQuizErrors = quizErrors?.name_quiz_errors?.critical_error || false;
            const hasCriticalQuestionErrors = quizErrors?.critical_errors?.length > 0;
            setExpandedSections(prev => ({
                ...prev,
                quizErrors: hasCriticalQuizErrors || (quizErrors?.name_quiz_errors && Object.keys(quizErrors.name_quiz_errors).length > 0),
                questionErrors: hasCriticalQuestionErrors || (quizErrors?.critical_errors?.length > 0 || quizErrors?.cosmetic_errors?.length > 0 ||
                    quizErrors?.logical_errors?.length > 0 || quizErrors?.minor_errors?.length > 0),
                criticalQuizErrors: hasCriticalQuizErrors,
                criticalQuestionErrors: hasCriticalQuestionErrors,
            }));
        }
    }, [quizErrors]);

    //Функция для обновления состояния названия викторины
    const handleNameQuizChange = (value) => {
        setQuizName(value);
    };

    //Функция для обновления состояния чекбоксов ошибок
    const handleCheckboxChange = (key) => (e) => {
        setCheckboxes({ ...checkboxes, [key]: e.target.checked });
    };

    //Функция для обновления состояния для отображения ошибок (всех ошибок)
    const processResponse = (response) => {
        const hasCriticalQuizErrors = response?.name_quiz_errors?.critical_error || false;
        const hasCriticalQuestionErrors = response?.critical_errors?.length > 0;

        setExpandedSections({
            quizErrors: hasCriticalQuizErrors || (response?.name_quiz_errors && Object.keys(response.name_quiz_errors).length > 0),
            questionErrors: hasCriticalQuestionErrors || (response?.critical_errors?.length > 0 || response?.cosmetic_errors?.length > 0 ||
                response?.logical_errors?.length > 0 || response?.minor_errors?.length > 0),
            criticalQuizErrors: hasCriticalQuizErrors,
            syntaxQuizErrors: false,
            criticalQuestionErrors: hasCriticalQuestionErrors,
            logicQuestionErrors: false,
            minorQuestionErrors: false,
            syntaxQuestionErrors: false,
        });
    };

    // Функция для сброса всех меток (чекбоксов) на исправления ошибок
    const resetCheckboxes = () => {
        setCheckboxes({
            cosmeticErrorQuizName: false,
            cosmeticErrors: false,
            minorErrors: false,
            logicalErrors: false,
        });
    };

    // НАЧАЛЬНОЕ ЗАВЕРШЕНИЕ ВИКТОРИНЫ
    // Функция для отправки данных по викторине и принятия результатов в виде ошибок викторины;
    const handleFinishQuiz = async () => {
        try {
            const response = await sendQuestionsToSearchError(quizName, questions);
            setQuizErrors(response); // Обновление ошибок викторины
            processResponse(response); // Обновление состояния для отображения ошибок (всех ошибок)
            resetCheckboxes(); // Сброс меток (чекбоксов) по исправлению ошибок викторины
            setIsModalOpen(true); // Отображение модульного окна
            console.log("Данные от сервера успешно сохранены:", response);
        } catch (error) {
            console.error('Ошибка при отправке вопросов:', error);
            alert("Техническая ошибка: невозможно создать викторину.");
        }
    };

    // АНАЛИЗ ВИКТОРИНЫ
    // Функция для отправки данных по викторине и принятия результатов в виде ошибок викторины;
    const handleAnalyzeQuiz = async () => {
        try {
            const response = await sendQuestionsToSearchError(quizName, questions);
            setQuizErrors(response); // Обновление ошибок викторины
            processResponse(response); // Обновление состояния для отображения ошибок (всех ошибок)
            resetCheckboxes(); // Сброс меток (чекбоксов) по исправлению ошибок викторины
            console.log("Данные от сервера успешно сохранены:", response);
        } catch (error) {
            console.error('Ошибка при отправке вопросов:', error);
            alert("Техническая ошибка: невозможно произвести анализ викторины на присутствие ошибок.");
        }
    };

    // ИСПРАВЛЕНИЕ ОШИБОК ВИКТОРИНЫ (+ АНАЛИЗ ВИКТОРИНЫ)
    // Функция для отправки данных по викторине и её ошибок и принятия результатов
    // в виде исправленных данных викторины и её обновлённых ошибок;
    const handleFixErrors = async () => {
        try {
            const response = await sendQuestionsToFixError(quizName, questions, checkboxes);
            setQuizName(response.quizName); // Обновление данных по названию викторины
            setQuestions(response.questions); // Обновление данных вопросов викторины
            setQuizErrors(response.errors); // Обновление ошибок викторины
            processResponse(response); // Обновление состояния для отображения ошибок (всех ошибок)
            resetCheckboxes(); // Обновление состояния для отображения ошибок (всех ошибок
            console.log("Данные от сервера успешно сохранены:", response);
            navigate(`/createQuestion/1`); //Дефолтное переключение на первую страницу
        } catch (error) {
            console.error('Ошибка при отправке вопросов:', error);
            alert("Техническая ошибка: невозможно исправить ошибки викторины.");
        }
    };


    // КОНЕЧНОЕ ЗАВЕРШЕНИЕ ВИКТОРИНЫ
    // Функция для отправки данных по викторине, исправление отмеченных ошибок и отправка данных в БД для записи;
    const handleConfirmFinishQuiz = async () => {
        try {
            checkboxes.minorErrors = true;
            const response = await sendQuestionsToRecordInBD(quizName, questions, checkboxes);
            if (response.status === 'success') {
                console.log("Операция успешна, индекс операции:", response.operation_index);
                alert("Викторина успешно создана!")
                navigate(`/`);
                setQuestions([]); //Обнуление контекста по вопросам записанной в бд викторине
            } else {
                console.error("Ошибка при выполнении операции:", response);
                alert("Техническая ошибка: невозможно создать викторину.");
            }
        } catch (error) {
            console.error('Ошибка при отправке вопросов:', error);
            alert("Техническая ошибка: невозможно создать викторину.");
        }
    };

    //Метка на группу всех незначительных ошибок:
    // косметические (вопросов и названия викторины), логический, несущественные.
    const hasErrors_NOT_significant =
        (quizErrors?.cosmetic_errors && quizErrors.cosmetic_errors.length > 0) ||
        (quizErrors?.logical_errors && quizErrors.logical_errors.length > 0) ||
        (quizErrors?.minor_errors && quizErrors.minor_errors.length > 0) ||
        (quizErrors?.name_quiz_errors && quizErrors.name_quiz_errors.cosmetic_error);

    //Метка на группу всех значительных ошибок:
    // критические (вопросов и названия викторины).
    const hasErrors_significant =
        (quizErrors?.critical_errors && quizErrors.critical_errors.length > 0) ||
        (quizErrors?.name_quiz_errors && quizErrors.name_quiz_errors.critical_error);

    const toggleSection = (section) => {
        setExpandedSections(prev => ({
            ...prev,
            [section]: !prev[section]
        }));
    };

    const renderErrors = () => {
        if (!quizErrors) return null;
        //console.log("Rendering errors with data:", quizErrors);

        return (
            <div className="error-container">
                {/* Ошибки викторин */}
                {quizErrors.name_quiz_errors && Object.keys(quizErrors.name_quiz_errors).length > 0 && (
                    <div className="error-section">
                        <h3 onClick={() => toggleSection('quizErrors')} className="section-title">
                            Ошибки викторины {expandedSections.quizErrors ? '▼' : '▶'}
                        </h3>
                        {expandedSections.quizErrors && (
                            <div className="error-subsections">
                                {/* Критические ошибки (развернуты по умолчанию) */}
                                {quizErrors.name_quiz_errors.critical_error && (
                                    <div className="error-subsection">
                                        <h4 onClick={() => toggleSection('criticalQuizErrors')} className="subsection-title">
                                            Критические {expandedSections.criticalQuizErrors ? '▼' : '▶'}
                                        </h4>
                                        {expandedSections.criticalQuizErrors && (
                                            <div className="error-item">
                                                <span style={{ color: 'red' }}> - {quizErrors.name_quiz_errors.critical_error}</span>
                                            </div>
                                        )}
                                    </div>
                                )}
                                {/* Косметические ошибки (свернуты по умолчанию) */}
                                {quizErrors.name_quiz_errors.cosmetic_error && (
                                    <div className="error-subsection">
                                        <h4 onClick={() => toggleSection('syntaxQuizErrors')} className="subsection-title">
                                            Косметические {expandedSections.syntaxQuizErrors ? '▼' : '▶'}
                                        </h4>
                                        {expandedSections.syntaxQuizErrors && (
                                            <div className="error-item">
                                                <span> - {quizErrors.name_quiz_errors.cosmetic_error}</span>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                )}

                {/* Ошибки вопросов */}
                {(quizErrors.critical_errors?.length > 0 || quizErrors.cosmetic_errors?.length > 0 ||
                    quizErrors.logical_errors?.length > 0 || quizErrors.minor_errors?.length > 0) && (
                    <div className="error-section">
                        <h3 onClick={() => toggleSection('questionErrors')} className="section-title">
                            Ошибки вопросов {expandedSections.questionErrors ? '▼' : '▶'}
                        </h3>
                        {expandedSections.questionErrors && (
                            <div className="error-subsections">
                                {/* Критические ошибки (развернуты по умолчанию) */}
                                {quizErrors.critical_errors && quizErrors.critical_errors.length > 0 && (
                                    <div className="error-subsection">
                                        <h4 onClick={() => toggleSection('criticalQuestionErrors')} className="subsection-title">
                                            Критические {expandedSections.criticalQuestionErrors ? '▼' : '▶'}
                                        </h4>
                                        {expandedSections.criticalQuestionErrors && quizErrors.critical_errors.map((questionError, index) => (
                                            questionError.errors.map((error, errorIndex) => (
                                                <div key={`${index}-${errorIndex}`} className="error-item">
                                                <span style={{ color: 'red' }}>
                                                    - <Link to={`/createQuestion/${questionError.id_question}`} style={{ color: 'red', textDecoration: 'underline' }}>
                                                        Вопрос {questionError.id_question}
                                                    </Link>: {error.text_error}
                                                </span>
                                                </div>
                                            ))
                                        ))}
                                    </div>
                                )}
                                {/* Логические ошибки (свернуты по умолчанию)*/}
                                {quizErrors.logical_errors && quizErrors.logical_errors.length > 0 && (
                                    <div className="error-subsection">
                                        <h4 onClick={() => toggleSection('logicQuestionErrors')} className="subsection-title">
                                            Логические {expandedSections.logicQuestionErrors ? '▼' : '▶'}
                                        </h4>
                                        {expandedSections.logicQuestionErrors && quizErrors.logical_errors.map((questionError, index) => (
                                            questionError.errors.map((error, errorIndex) => (
                                                <div key={`${index}-${errorIndex}`} className="error-item">
                                                <span>
                                                    - <Link to={`/createQuestion/${questionError.id_question}`} style={{ color: 'inherit', textDecoration: 'underline' }}>
                                                        Вопрос {questionError.id_question}
                                                    </Link>: {error.text_error}
                                                </span>
                                                </div>
                                            ))
                                        ))}
                                    </div>
                                )}
                                {/* Косметические ошибки (свернуты по умолчанию)*/}
                                {quizErrors.cosmetic_errors && quizErrors.cosmetic_errors.length > 0 && (
                                    <div className="error-subsection">
                                        <h4 onClick={() => toggleSection('syntaxQuestionErrors')} className="subsection-title">
                                            Косметические {expandedSections.syntaxQuestionErrors ? '▼' : '▶'}
                                        </h4>
                                        {expandedSections.syntaxQuestionErrors && quizErrors.cosmetic_errors.map((questionError, index) => (
                                            questionError.errors.map((error, errorIndex) => (
                                                <div key={`${index}-${errorIndex}`} className="error-item">
                                                <span>
                                                    - <Link to={`/createQuestion/${questionError.id_question}`} style={{ color: 'inherit', textDecoration: 'underline' }}>
                                                        Вопрос {questionError.id_question}
                                                    </Link>: {error.text_error}
                                                </span>
                                                </div>
                                            ))
                                        ))}
                                    </div>
                                )}
                                {/* Несущественные ошибки (свернуты по умолчанию)*/}
                                {quizErrors.minor_errors && quizErrors.minor_errors.length > 0 && (
                                    <div className="error-subsection">
                                        <h4 onClick={() => toggleSection('minorQuestionErrors')} className="subsection-title">
                                            Несущественные {expandedSections.minorQuestionErrors ? '▼' : '▶'}
                                        </h4>
                                        {expandedSections.minorQuestionErrors && quizErrors.minor_errors.map((questionError, index) => (
                                            questionError.errors.map((error, errorIndex) => (
                                                <div key={`${index}-${errorIndex}`} className="error-item">
                                                <span>
                                                    - <Link to={`/createQuestion/${questionError.id_question}`} style={{ color: 'inherit', textDecoration: 'underline' }}>
                                                        Вопрос {questionError.id_question}
                                                    </Link>: {error.text_error}
                                                </span>
                                                </div>
                                            ))
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                )}
            </div>
        );
    };

    return (
        <div id="creatorLayout" style={{ display: 'flex', flexDirection: 'row', height: '100vh', margin: '0 auto', boxSizing: 'border-box' }}>
            {/* Левая часть */}
            <div style={{ flex: '1', padding: '20px', borderRight: '1px solid rgba(0, 0, 0, 0.2)', backgroundColor: 'rgba(0, 0, 0, 0.1)', boxSizing: 'border-box', minHeight: '100%' }}>
                {renderErrors()}
            </div>

            {/* Центральная часть */}
            <div style={{ flex: '2', display: 'flex', flexDirection: 'column', padding: '10px', boxSizing: 'border-box' }}>
                <header style={{ textAlign: 'center', marginTop: '40px' }}>
                    <h1 style={{ margin: '0' }}>Создание викторины</h1>
                </header>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', marginTop: '20px' }}>
                    <span style={{ marginRight: '10px', fontSize: '20px' }}>Название викторины:</span>
                    <input
                        type="text"
                        value={quizName}
                        onChange={(e) => handleNameQuizChange(e.target.value)}
                        maxLength={maxNameQuizLength}
                        style={{ border: 'none', borderBottom: '1px solid black', outline: 'none', width: '200px', textAlign: 'center', fontSize: '16px', padding: '5px 0', boxSizing: 'border-box' }}
                        placeholder="Введите название"
                    />
                </div>
                {quizName.length >= maxNameQuizLength && (
                    <div style={{ color: 'red', marginTop: '5px' }}>
                        Достигнут максимум символов ({maxNameQuizLength} символов).
                    </div>
                )}
                <main style={{ textAlign: 'center', width: '100%', marginTop: '20px', flexGrow: 1 }}>
                    <Outlet />
                </main>
                <footer style={{ display: 'flex', justifyContent: 'center', marginTop: '20px', paddingBottom: '20px', paddingTop: '10px' }}>
                    <button className="btn btn-primary" onClick={handleAnalyzeQuiz} style={{ padding: '10px 10px', fontSize: '14px' }}>
                        Анализ викторины
                    </button>
                    <button className="btn btn-success" onClick={handleFinishQuiz} style={{ marginLeft: '20px', padding: '10px 20px', fontSize: '14px' }}>
                        Завершить викторину
                    </button>
                </footer>
            </div>

            {/* Всплывающее окно */}
            {isModalOpen && (
                <div className="modal">
                    <div className="modal-content">
                        <span className="close" onClick={() => setIsModalOpen(false)}>&times;</span>
                        {/* Случай 1: нет критических ошибок, но есть другие ошибки*/}
                        {hasErrors_NOT_significant && !hasErrors_significant && (
                            <div>
                                <h3>Ошибки викторины</h3>
                                <p>В викторине нет критических ошибок, но есть ряд других ошибок:</p>
                                {/* Косметические ошибки (в названии викторины)*/}
                                {quizErrors?.name_quiz_errors?.cosmetic_error && (
                                    <div className="checkbox-container">
                                        <label>
                                            <input
                                                type="checkbox"
                                                checked={checkboxes.cosmeticErrorQuizName}
                                                onChange={handleCheckboxChange('cosmeticErrorQuizName')}
                                            />
                                            Косметические (название викторины)
                                        </label>
                                    </div>
                                )}
                                {/* Логические ошибки */}
                                {quizErrors?.logical_errors && quizErrors.logical_errors.length > 0 && (
                                    <div className="checkbox-container">
                                        <label>
                                            <input
                                                type="checkbox"
                                                checked={checkboxes.logicalErrors}
                                                onChange={handleCheckboxChange('logicalErrors')}
                                            />
                                            Логические (вопросы)
                                        </label>
                                    </div>
                                )}
                                {/* Косметические ошибки (в вопросах викторины)*/}
                                {quizErrors?.cosmetic_errors && quizErrors.cosmetic_errors.length > 0 && (
                                    <div className="checkbox-container">
                                        <label>
                                            <input
                                                type="checkbox"
                                                checked={checkboxes.cosmeticErrors}
                                                onChange={handleCheckboxChange('cosmeticErrors')}
                                            />
                                            Косметические (вопросы)
                                        </label>
                                    </div>
                                )}
                                {/* Несущественные ошибки (в вопросах викторины)*/}
                                {quizErrors?.minor_errors && quizErrors.minor_errors.length > 0 && (
                                    <p>
                                        - несущественные ошибки (будут исправлены автоматически).
                                    </p>
                                )}
                                {((quizErrors?.cosmetic_errors && quizErrors.cosmetic_errors.length > 0) ||
                                    (quizErrors?.logical_errors && quizErrors.logical_errors.length > 0) ||
                                    (quizErrors?.name_quiz_errors && quizErrors.name_quiz_errors.cosmetic_error)
                                ) && !quizErrors?.minor_errors?.length > 0 && (
                                    <p>
                                        Перед нажатием на Кнопку Подтвердить можете выбрать <br />
                                        автоматическое исправление выше представленных типов ошибок.
                                    </p>
                                )}

                                <button onClick={handleConfirmFinishQuiz}>Подтвердить</button>
                            </div>
                        )}

                        {/* Случай 2: есть критические ошибки */}
                        {hasErrors_significant && (
                            <div>
                                <h3>Критические ошибки</h3>
                                <p>В викторине присутствуют критические ошибки. Завершение викторины невозможно.</p>
                                <p>Исправьте критические ошибки, чтобы завершить данную викторину.</p>
                                <button onClick={() => {setIsModalOpen(false)}}>OK</button>
                            </div>
                        )}

                        {/* Случай 3: нет ни критических ошибок, ни других ошибок*/}
                        {!hasErrors_significant && !hasErrors_NOT_significant && (
                            <div>
                                <h3>Нет ошибок</h3>
                                <p>В викторине ошибок не найдено. Желаете сохранить викторину?</p>
                                <button onClick={handleConfirmFinishQuiz}>Подтвердить</button>
                            </div>
                        )}
                    </div>
                </div>
            )}


            {/* Правая часть */}
            <div style={{ flex: '1', padding: '20px', backgroundColor: 'rgba(0, 0, 0, 0.1)', boxSizing: 'border-box', minHeight: '100%' }}>
                {/* Табличка с ошибками */}
                {hasErrors_NOT_significant && (
                    <div style={{ border: '1px solid #ccc', borderRadius: '5px', padding: '15px', backgroundColor: '#fff', boxShadow: '0 2px 10px rgba(0, 0, 0, 0.1)', marginBottom: '20px' }}>
                        <h2 style={{ fontSize: '18px', textAlign: 'center' }}>Исправить ошибки:</h2>
                        {/* Косметические ошибки (в названии викторины) */}
                        {quizErrors?.name_quiz_errors?.cosmetic_error && (
                            <div style={{ marginBottom: '10px' }}>
                                <div className="checkbox-container">
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={checkboxes.cosmeticErrorQuizName}
                                            onChange={handleCheckboxChange('cosmeticErrorQuizName')}
                                        />
                                        Косметические (название викторины)
                                    </label>
                                </div>
                            </div>
                        )}
                        {/* Логические ошибки */}
                        {quizErrors?.logical_errors && quizErrors.logical_errors.length > 0 && (
                            <div style={{ marginBottom: '10px' }}>
                                <div className="checkbox-container">
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={checkboxes.logicalErrors}
                                            onChange={handleCheckboxChange('logicalErrors')}
                                        />
                                        Логические (вопросы)
                                    </label>
                                </div>
                            </div>
                        )}
                        {/* Косметические ошибки (в вопросах викторины) */}
                        {quizErrors?.cosmetic_errors && quizErrors.cosmetic_errors.length > 0 && (
                            <div style={{ marginBottom: '10px' }}>
                                <div className="checkbox-container">
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={checkboxes.cosmeticErrors}
                                            onChange={handleCheckboxChange('cosmeticErrors')}
                                        />
                                        Косметические (вопросы)
                                    </label>
                                </div>
                            </div>
                        )}
                        {/* Несущественные ошибки */}
                        {quizErrors?.minor_errors && quizErrors.minor_errors.length > 0 && (
                            <div style={{ marginBottom: '10px' }}>
                                <div className="checkbox-container">
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={checkboxes.minorErrors}
                                            onChange={handleCheckboxChange('minorErrors')}
                                        />
                                        Несущественные (вопросы)
                                    </label>
                                </div>
                            </div>
                        )}
                        <button className="btn btn-primary" style={{ width: '100%', padding: '10px', fontSize: '14px' }} onClick={handleFixErrors}>
                            Исправить
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
