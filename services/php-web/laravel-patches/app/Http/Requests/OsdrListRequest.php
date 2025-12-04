<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OsdrListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sort_col' => ['nullable', 'in:dataset_id,title,updated_at,inserted_at'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'q'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function sanitized(): array
    {
        $v = $this->validated();

        return [
            'sort_col' => $v['sort_col'] ?? 'inserted_at',
            'sort_dir' => $v['sort_dir'] ?? 'desc',
            'q'        => trim($v['q'] ?? ''),
        ];
    }
}
