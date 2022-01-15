<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class FileResource
 *
 * @property string id
 * @property string uploader_id
 */
class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->resource->toArray(),
            'links' => [
                'self' => [
                    'id' => $this->id,
                    'link' => route('api.files.view', $this->id),
                ],
                'uploader' => [
                    'id' => $this->uploader_id,
                    'link' => route('api.users.view', $this->uploader_id),
                ],
            ]
        ];
    }
}
