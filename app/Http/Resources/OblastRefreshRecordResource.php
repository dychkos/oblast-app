<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OblastRefreshRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'createTs' => $this->create_ts,
            'delayTs' => $this->delay_ts,
            'state' => $this->state->value,
        ];
    }
}
