<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AstroEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lat'  => ['required', 'numeric', 'between:-90,90'],
            'lon'  => ['required', 'numeric', 'between:-180,180'],
            'days' => ['required', 'integer', 'between:1,30'],
        ];
    }
}
