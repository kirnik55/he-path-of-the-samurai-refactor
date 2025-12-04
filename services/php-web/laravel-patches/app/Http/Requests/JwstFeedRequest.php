<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JwstFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source'    => ['sometimes', 'in:jpg,suffix,program'],
            'suffix'    => ['nullable', 'string', 'max:50'],
            'program'   => ['nullable', 'string', 'max:50'],
            'instrument'=> ['nullable', 'string', 'in:NIRCam,MIRI,NIRISS,NIRSpec,FGS'],
            'page'      => ['sometimes', 'integer', 'min:1'],
            'perPage'   => ['sometimes', 'integer', 'min:1', 'max:60'],
        ];
    }
}
