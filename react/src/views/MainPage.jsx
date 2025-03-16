import {useNavigate} from "react-router-dom";
//import React from "react";

export default function MainPage() {
    const navigate = useNavigate();

    const createQuiz = () => {
        navigate("/createQuestion/1");
    };

  return (
    <div>
        <div>MainPage</div>
      <button onClick={createQuiz}>Создать викторину</button>
    </div>
  )
}
