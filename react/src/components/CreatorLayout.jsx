import React, {useState} from 'react';
import { Outlet } from "react-router-dom";
import { useContext } from 'react';
import { QuestionContext } from '../context/QuestionContext';
import {sendQuestions} from "../SenderQuiz.jsx";

export default function CreatorLayout() {
    const {questions } = useContext(QuestionContext);

    const [quizName, setQuizName] = useState('');
    const maxNameQuizLength = 200; // Максимальная длина

    const handleFinish = () => {
        try {
            const response = sendQuestions(quizName, questions);
        } catch (error) {
            console.error('Ошибка при отправке вопросов:', error);
        }
    };

    const handleNameQuizChange = (event) => {
        setQuizName(event.target.value);
    };

    return (
        <div
            id="creatorLayout"
            style={{
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                width: '100%',
                maxWidth: '1200px',
                margin: '0 auto',
            }}
        >
            <header style={{ textAlign: 'center' }}>
                <h1>Я в CreatorLayout</h1>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', marginTop: '10px' }}>
                    <span style={{ marginRight: '10px', fontSize: '20px' }}>Название викторины:</span>
                    <input
                        type="text"
                        value={quizName}
                        onChange={handleNameQuizChange}
                        maxLength={maxNameQuizLength}
                        style={{
                            border: 'none',
                            borderBottom: '1px solid black', // Подчеркивание
                            outline: 'none',
                            width: '200px', // Ширина поля
                            textAlign: 'center', // Выравнивание текста по центру
                            fontSize: '16px', // Размер шрифта
                            padding: '5px 0', // Вертикальное выравнивание текста
                            boxSizing: 'border-box', // Учитывать padding в ширине
                        }}
                        placeholder="Введите название" // Текст-подсказка
                    />
                </div>
                {quizName.length >= maxNameQuizLength && (
                    <div style={{ color: 'red', marginTop: '5px' }}>
                        Достигнут максимум символов ({maxNameQuizLength} символов).
                    </div>
                )}
            </header>
            <main style={{ textAlign: 'center', width: '100%' }}>
                <Outlet />
            </main>
            <div style={{ display: 'flex', justifyContent: 'center', marginTop: '20px' }}>
                <button className="btn btn-success" onClick={handleFinish}>
                    Завершить
                </button>
            </div>
        </div>
    );


}
