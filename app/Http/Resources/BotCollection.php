<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BotCollection extends ResourceCollection
{
    public function with($request): array
    {
        return [
            'ok' => true,
        ];
    }
}
