<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OsdrIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        // авторизацию тут не ограничиваем
        return true;
    }

    public function rules(): array
    {
        return [
            // лимит строк OSDR из Rust-сервиса
            'limit' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }
}
