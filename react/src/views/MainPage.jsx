import { useNavigate } from 'react-router-dom';
import { checkActivityServerAndBD } from '../SenderQuizCreating.js';
import { useState, useRef, useEffect } from 'react';
import './MainPage.css';
import { searchQuiz } from '../SenderQuizPassing.js';
import { useQuizContext } from '../context/QuizContext.jsx';

export default function MainPage() {
    const { setQuiz } = useQuizContext();

    const navigate = useNavigate();
<<<<<<< HEAD
    const [quizName, setQuizName] = useState(""); // Данные викторины
=======
    const [quizName, setQuizName] = useState('');
>>>>>>> markast
    const [errors, setErrors] = useState(null);
    const [foundQuizzes, setFoundQuizzes] = useState([]);
    const [showQuizList, setShowQuizList] = useState(false);
    const searchWrapperRef = useRef(null);
    const [loading, setLoading] = useState(false); // Загрузка

    // Форматирование даты
    const formatDate = (dateString) => {
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        };
        return new Date(dateString).toLocaleDateString('ru-RU', options);
    };

    // Закрытие dropdown при клике вне области
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (searchWrapperRef.current && !searchWrapperRef.current.contains(event.target)) {
                setShowQuizList(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Нажатие кнопки создания викторины
    const handleCreateQuiz = async () => {
        setLoading(true);
        try {
            const result = await checkActivityServerAndBD();

            if (result?.error) {
                console.error('Ошибка c сервером или БД:', result.error);
                setErrors({
                    server: ['Технические ошибки. Пожалуйста, попробуйте позже.'],
                });
                return;
            }

            console.log('Результат проверки:', result);
            navigate('/createQuestion/1');
        } catch (error) {
            console.error('Ошибка:', error.data?.message || error.message);
            setErrors({
                server: ['Технические проблемы с сервером. Пожалуйста, попробуйте позже.'],
            });
        }
    };

    // Нажатие кнопки поиска викторины
    const handleSearchQuiz = async () => {
        setErrors(null);
        const query = quizName.trim();

        if (!query) {
            setErrors({ search: ['Введите название викторины'] });
            return;
        }

        setShowQuizList(false);
        setLoading(true);
        try {
            const { status, data } = await searchQuiz(query);
            setLoading(false);
            switch (status) {
                case 200:
                    setFoundQuizzes(data.quizzes);
                    setShowQuizList(true);
                    console.log(data.quizzes);
                    break;
                case 404:
                    setErrors({ search: ['Викторины не найдены'] });
                    setFoundQuizzes([]);
                    setShowQuizList(false);
                    break;
                case 422:
                    setErrors({ search: ['Некорректный запрос'] });
                    setFoundQuizzes([]);
                    setShowQuizList(false);
                    break;
                default:
                    setErrors({ server: ['Неизвестная ошибка сервера'] });
                    setFoundQuizzes([]);
                    setShowQuizList(false);
            }
        } catch (error) {
            console.error('Критическая ошибка:', error);
            setErrors({ server: ['Сервер недоступен. Попробуйте позже.'] });
            setFoundQuizzes([]);
            setShowQuizList(false);
        }
    };

    // Обработчик перехода к викторине
    const handleQuizNavigation = (quiz) => {
        setQuiz(quiz);
        navigate(`/passQuiz`);
    };

    return (
        <div className="main-page-container">
            <h2>Главная страница</h2>

            {errors && (
                <div className="alert">
                    {Object.keys(errors).map((key) => (
                        <p key={key}>{errors[key][0]}</p>
                    ))}
                </div>
            )}

            <button className="green-button" onClick={handleCreateQuiz}>
                Создать викторину
            </button>

            <div className="search-wrapper" ref={searchWrapperRef}>
                <input
                    type="text"
                    value={quizName}
                    onChange={(e) => setQuizName(e.target.value)}
                    placeholder="Введите запрос"
                    className="search-input"
                    onFocus={() => foundQuizzes.length > 0 && setShowQuizList(true)}
                />
                <button className="search-button" onClick={handleSearchQuiz}>
                    🔍
                </button>
            </div>

            {loading && <div className="loading-text show">Загрузка...</div>}

            {showQuizList && foundQuizzes.length > 0 && (
                <div className="quiz-dropdown-container">
                    <div className="quiz-dropdown-frame">
                        {foundQuizzes.map((quiz) => (
                            <div
                                key={quiz.id_quiz}
                                className="quiz-dropdown-item"
                                onMouseDown={() => handleQuizNavigation(quiz)}
                            >
                                <div className="quiz-name">{quiz.name_quiz}</div>
                                <div className="quiz-details">
                                    <span>Автор: {quiz.login_user}</span>
                                    <span>Проходили: {quiz.was_taken} раз</span>
                                    <span>Создана: {formatDate(quiz.created_at)}</span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
