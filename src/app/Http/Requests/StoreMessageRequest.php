<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Повідомлення не може бути порожнім.',
            'body.string' => 'Повідомлення повинно бути рядком.',
            'body.max' => 'Повідомлення не повинно перевищувати 1000 символів.',
        ];
    }
}
