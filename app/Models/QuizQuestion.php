<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quiz_question_answers';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_quiz_question_answers';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_quiz_question_answers',
        'text_question',
        'correct_option',
        'wrong_option',
        'id_quiz',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'wrong_option' => 'array', // Преобразуем JSON в массив
    ];

    /**
     * Get the quiz that owns the question answer.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'id_quiz');
    }
}
