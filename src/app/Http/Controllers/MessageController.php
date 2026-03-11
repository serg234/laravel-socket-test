<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMessageRequest;
use App\Events\MessageSent;

/**
 * Контролер для роботи з особистими повідомленнями між користувачами.
 */
class MessageController extends Controller
{
    /**
     * Відображає чат між авторизованим користувачем і обраним користувачем.
     */
    public function show(User $user): View
    {
        $authId = auth()->id();

        // Забороняємо відкривати чат із самим собою
        abort_if($authId === $user->id, 404);

        // Отримуємо всі повідомлення між двома користувачами
        $messages = Message::getChatBetweenUsers($authId, $user->id);

        // Передаємо дані у view
        return view('messages.show', [
            'chatUser' => $user,
            'messages' => $messages,
        ]);
    }

    /**
     * Зберігає нове повідомлення та відправляє broadcast-подію.
     */
    public function store(StoreMessageRequest $request, User $user): RedirectResponse | \Illuminate\Http\JsonResponse
    {
        $authUser = $request->user();

        // Забороняємо відправляти повідомлення самому собі
        abort_if($authUser->id === $user->id, 404);

        // Отримуємо валідовані дані з request
        $validated = $request->validated();

        // Створюємо нове повідомлення
        $message = Message::query()->create([
            'sender_id' => $authUser->id,
            'receiver_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Завантажуємо зв'язки для коректної передачі у broadcast
        $message->load(['sender', 'receiver']);

        // Відправляємо подію через WebSocket іншим клієнтам
        broadcast(new MessageSent($message))->toOthers();

        // Якщо запит очікує JSON (AJAX / API)
        if ($request->expectsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->format('Y-m-d H:i:s'),
                    'sender_name' => $message->sender?->name,
                    'receiver_name' => $message->receiver?->name,
                ],
            ]);
        }

        // Якщо звичайний HTTP запит — редірект назад у чат
        return redirect()
            ->route('messages.show', $user)
            ->with('success', 'Повідомлення відправлено.');
    }
}
