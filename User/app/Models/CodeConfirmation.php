<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeConfirmation extends Model
{
    use HasFactory;

    protected $table = 'code_confirmations'; // Название таблицы, связанной с моделью

    protected $primaryKey = 'id_code_confirmation'; // Первичный ключ таблицы
    public $keyType = 'uuid'; // Тип первичного ключа
    public $incrementing = false; // Первичный ключ не является автоинкрементным
    public $timestamps = true;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_code_confirmation',
        'email',
        'code_confirmation'
    ];

    /**
     * Атрибуты, которые должны быть скрыты при сериализации.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id_code_confirmation',
    ];

    /**
     * Атрибуты, которые должны быть приведены к определённым типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email' => 'string',
    ];
}
