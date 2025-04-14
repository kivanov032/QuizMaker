import { useNavigate } from "react-router-dom";
import { checkActivityServerAndBD } from "../SenderQuiz.js";
import { useState } from "react";
import "./MainPage.css";

export default function MainPage() {
    const navigate = useNavigate();
    const [searchQuery, setSearchQuery] = useState("");

    const createQuiz = async () => {
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

    const handleSearch = () => {
        console.log("Поиск по запросу:", searchQuery);
    };

    return (
        <div className="main-page-container">
            <h2>Главная страница</h2>

            <button className="create-quiz-button" onClick={createQuiz}>
                Создать викторину
            </button>

            <div className="search-wrapper">
                <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Введите запрос"
                    className="search-input"
                />
                <button className="search-button" onClick={handleSearch}>
                    🔍
                </button>
            </div>

        </div>
    );
}
