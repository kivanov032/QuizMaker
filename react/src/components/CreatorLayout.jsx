import React, { useState, useContext } from 'react';
import { Outlet } from "react-router-dom";
import { QuestionContext } from '../context/QuestionContext';
import { sendQuestions } from "../SenderQuiz.jsx";

export default function CreatorLayout() {
    const { questions } = useContext(QuestionContext);
    const [quizName, setQuizName] = useState('');
    const [quizErrors, setQuizErrors] = useState(null);
    const maxNameQuizLength = 200;

    const handleFinish = async () => {
        try {
            const response = await sendQuestions(quizName, questions);
            setQuizErrors(response);
            console.log("Данные от сервера успешно сохранены:", response);
        } catch (error) {
            console.error('Ошибка при отправке вопросов:', error);
        }
    };

    const handleNameQuizChange = (event) => {
        setQuizName(event.target.value);
    };

    const handleAnalyze = () => {
        // Логика для анализа викторины
    };

    const handleFixErrors = () => {
        // Логика для исправления ошибок
        console.log("Исправление ошибок...");
    };

    const hasErrors_NOT_significant =
        (quizErrors?.cosmetic_errors && quizErrors.cosmetic_errors.length > 0) ||
        (quizErrors?.logical_errors && quizErrors.logical_errors.length > 0) ||
        (quizErrors?.minor_errors && quizErrors.minor_errors.length > 0) ||
        (quizErrors?.name_quiz_errors && quizErrors.name_quiz_errors.cosmetic_error);

    const hasErrors_significant =
        (quizErrors?.critical_errors && quizErrors.critical_errors.length > 0) ||
        (quizErrors?.name_quiz_errors && quizErrors.name_quiz_errors.critical_error);

    return (
        <div id="creatorLayout" style={{ display: 'flex', flexDirection: 'row', height: '100vh', margin: '0 auto', boxSizing: 'border-box' }}>
            {/* Левая часть */}
            <div style={{ flex: '1', padding: '20px', borderRight: '1px solid rgba(0, 0, 0, 0.2)', backgroundColor: 'rgba(0, 0, 0, 0.1)', boxSizing: 'border-box', minHeight: '100%' }}>
                <h2>Левая секция</h2>
                <p>Некоторый текст или элементы.</p>

                {/* Табличка с ошибками */}
                {hasErrors_significant && (
                    <div style={{ border: '1px solid #ccc', borderRadius: '5px', padding: '15px', backgroundColor: '#fff', boxShadow: '0 2px 10px rgba(0, 0, 0, 0.1)', marginBottom: '20px' }}>
                        <h2 style={{ fontSize: '18px', textAlign: 'center' }}>Встречены критические ошибки:</h2>
                        {quizErrors?.name_quiz_errors?.critical_error && (
                            <div style={{ marginBottom: '10px', textAlign: 'center' }}>
                                <span style={{ color: 'red' }}> - в названии викторины!</span>
                            </div>
                        )}
                        {quizErrors?.critical_error && quizErrors.critical_error.length > 0 && (
                            <div style={{ marginBottom: '10px', textAlign: 'center' }}>
                                <span style={{ color: 'red' }}> - в вопросах викторины!</span>
                            </div>
                        )}
                    </div>
                )}


            </div>

            {/* Центральная часть */}
            <div style={{ flex: '2', display: 'flex', flexDirection: 'column', padding: '10px', boxSizing: 'border-box' }}>
                <header style={{ textAlign: 'center', marginTop: '40px' }}>
                    <h1 style={{ margin: '0' }}>Я в CreatorLayout</h1>
                </header>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', marginTop: '20px' }}>
                    <span style={{ marginRight: '10px', fontSize: '20px' }}>Название викторины:</span>
                    <input
                        type="text"
                        value={quizName}
                        onChange={handleNameQuizChange}
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
                    <button className="btn btn-primary" onClick={handleAnalyze} style={{ padding: '10px 10px', fontSize: '14px' }}>
                        Анализ викторины
                    </button>
                    <button className="btn btn-success" onClick={handleFinish} style={{ marginLeft: '20px', padding: '10px 20px', fontSize: '14px' }}>
                        Завершить викторину
                    </button>
                </footer>
            </div>

            {/* Правая часть */}
            <div style={{ flex: '1', padding: '20px', backgroundColor: 'rgba(0, 0, 0, 0.1)', boxSizing: 'border-box', minHeight: '100%' }}>
                <h2>Правая секция</h2>
                <p>Некоторый текст или элементы.</p>

                {/* Табличка с ошибками */}
                {hasErrors_NOT_significant && (
                    <div style={{ border: '1px solid #ccc', borderRadius: '5px', padding: '15px', backgroundColor: '#fff', boxShadow: '0 2px 10px rgba(0, 0, 0, 0.1)', marginBottom: '20px' }}>
                        <h2 style={{ fontSize: '18px', textAlign: 'center' }}>Исправить ошибки:</h2>
                        {quizErrors?.name_quiz_errors?.cosmetic_error && (
                            <div style={{ marginBottom: '10px' }}>
                                <label>
                                    <input type="checkbox" /> Косметические (название викторины)
                                </label>
                            </div>
                        )}
                        {quizErrors?.cosmetic_errors && quizErrors.cosmetic_errors.length > 0 && (
                            <div style={{ marginBottom: '10px' }}>
                                <label>
                                    <input type="checkbox" /> Косметические (вопросы)
                                </label>
                            </div>
                        )}
                        {quizErrors?.minor_errors && quizErrors.minor_errors.length > 0 && (
                            <div style={{ marginBottom: '10px' }}>
                                <label>
                                    <input type="checkbox" /> Несущественные (вопросы)
                                </label>
                            </div>
                        )}
                        {quizErrors?.logical_errors && quizErrors.logical_errors.length > 0 && (
                            <div style={{ marginBottom: '10px' }}>
                                <label>
                                    <input type="checkbox" /> Логические (вопросы)
                                </label>
                            </div>
                        )}
                        <button style={{ width: '100%', padding: '10px', fontSize: '14px' }} onClick={handleFixErrors}>
                            Исправить
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
