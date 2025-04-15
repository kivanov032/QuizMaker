import { useNavigate } from "react-router-dom";
import {checkActivityServerAndBD} from "../SenderQuizCreating.js";
import { useState } from "react";
import "./MainPage.css";
import {searchQuiz} from "../SenderQuizPassing.js";

export default function MainPage() {
    const navigate = useNavigate();
    const [quizName, setQuizName] = useState("");

    //Нажатие кнопки на создание викторины
    const handleCreateQuiz = async () => {
        try {
            const result = await checkActivityServerAndBD();

            if (result?.error) {
                console.error("Ошибка c сервером или БД:", result.error);
                alert("Технические ошибки. Пожалуйста, попробуйте позже.");
                return;
            }

            console.log("Результат проверки:", result);
            navigate("/createQuestion/1");
        } catch (error) {
            console.error("Ошибка:", error.data?.message || error.message);
            alert("Технические проблемы с сервером. Пожалуйста, попробуйте позже.");
        }
    };

    //Нажатие кнопки на поиск викторины по её названию
    const handleSearchQuiz = async () => {
        console.log("Поиск по запросу:", quizName);

        try {
            const response = await searchQuiz(quizName);
            if (response.status === 'success') {
                console.log("Операция успешна, индекс операции:", response.operation_index);
            } else {
                console.error("Ошибка при выполнении операции:", response);
                alert("Техническая ошибка: невозможно создать викторину.");
            }
        } catch (error) {
            console.error('Ошибка при отправке вопросов:', error);
            alert("Техническая ошибка: невозможно создать викторину.");
        }

    };


    return (
        <div className="main-page-container">
            <h2>Главная страница</h2>

            <button className="create-quiz-button" onClick={handleCreateQuiz}>
                Создать викторину
            </button>

            <div className="search-wrapper">
                <input
                    type="text"
                    value={quizName}
                    onChange={(e) => setQuizName(e.target.value)}
                    placeholder="Введите запрос"
                    className="search-input"
                />
                <button className="search-button" onClick={handleSearchQuiz}>
                    🔍
                </button>
            </div>

        </div>
    );
}
