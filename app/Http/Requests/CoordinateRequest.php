<?php

namespace App\Http\Requests;

class CoordinateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ];
    }
}
