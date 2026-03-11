<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Message;

/**
 * Модель користувача системи.
 * Використовується для автентифікації та роботи з повідомленнями.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Поля, доступні для масового заповнення (mass assignment).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Поля, які приховуються при серіалізації моделі (наприклад у JSON).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Приведення типів атрибутів моделі.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Повідомлення, які користувач відправив.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Повідомлення, які користувач отримав.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
