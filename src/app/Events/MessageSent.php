<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Подія, яка відправляється при створенні нового повідомлення
 * і транслюється через WebSocket (broadcast) отримувачу.
 */
class MessageSent implements ShouldBroadcast
{
    /**
     * Dispatchable — дозволяє викликати подію через dispatch()
     * SerializesModels — коректно серіалізує Eloquent моделі для черг
     */
    use Dispatchable, SerializesModels;

    /**
     * В конструктор передається модель повідомлення,
     * яку ми будемо транслювати через broadcasting.
     */
    public function __construct(
        public Message $message
    ) {
    }

    /**
     * Визначає канал, на який буде відправлена подія.
     * Тут використовується приватний канал конкретного користувача.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->message->receiver_id),
        ];
    }

    /**
     * Ім'я події, яке буде отримувати frontend (Echo / JS).
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Дані, які будуть передані на frontend разом із подією.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at?->toDateTimeString(),
            'sender_name' => $this->message->sender?->name,
            'receiver_name' => $this->message->receiver?->name,
        ];
    }
}
