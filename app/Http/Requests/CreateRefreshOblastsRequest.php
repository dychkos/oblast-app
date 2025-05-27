<?php

namespace App\Http\Requests;

class CreateRefreshOblastsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'delay' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
