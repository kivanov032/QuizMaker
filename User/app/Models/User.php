<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $table = 'users'; // Название таблицы, связанной с моделью

    protected $primaryKey = 'id_user'; // Первичный ключ таблицы
    public $keyType = 'uuid'; // Тип первичного ключа
    public $incrementing = false; // Первичный ключ не является автоинкрементным
    public $timestamps = true;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_user',
        'login',
        'email',
        'password',
        'created_quizzes_counter',
        'taken_quizzes_counter'
    ];

    /**
     * Атрибуты, которые должны быть скрыты при сериализации.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определённым типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

}
