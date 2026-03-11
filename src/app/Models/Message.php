<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель повідомлення між двома користувачами.
 */
class Message extends Model
{
    /**
     * Поля, доступні для mass assignment.
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'body',
    ];

    /**
     * Відправник повідомлення.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Отримувач повідомлення.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Повертає всі повідомлення між двома користувачами.
     */
    public static function getChatBetweenUsers(int $firstUserId, int $secondUserId): Collection
    {
        return static::query()
            ->where(function ($query) use ($firstUserId, $secondUserId) {
                $query->where('sender_id', $firstUserId)
                    ->where('receiver_id', $secondUserId);
            })
            ->orWhere(function ($query) use ($firstUserId, $secondUserId) {
                $query->where('sender_id', $secondUserId)
                    ->where('receiver_id', $firstUserId);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at')
            ->get();
    }
}
