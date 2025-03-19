import {useNavigate} from "react-router-dom";
import {checkConnectionWithBD} from "../SenderQuiz.jsx";

export default function MainPage() {
    const navigate = useNavigate();

    const createQuiz = async () => {
        try {
            const result = await checkConnectionWithBD();

            // Проверяем, есть ли ошибка в результате
            if (result?.error) {
                console.error('Ошибка c сервером или БД:', result.error);
                alert("Технические ошибки. Пожалуйста, попробуйте позже.");
                return;
            }

            console.log('Результат проверки:', result);
            navigate("/createQuestion/1");
        } catch (error) {
            console.error('Ошибка:', error.data?.message || error.message);
            alert("Технические проблемы с сервером. Пожалуйста, попробуйте позже.");
        }
    };

    return (
    <div>
        <div>MainPage</div>
      <button onClick={createQuiz}>Создать викторину</button>
    </div>
  )
}
