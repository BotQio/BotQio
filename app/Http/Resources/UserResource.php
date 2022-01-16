<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * @package App\Http\Resources
 *
 * @property int id
 * @property string username
 * @property string link
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->resource->attributesToArray(),
            'links' => [
                'self' => [
                    'id' => $this->id,
                    'link' => route('api.users.view', $this->id),
                ],
            ],
        ];
    }

    public function with($request): array
    {
        return [
            'ok' => true,
        ];
    }
}
