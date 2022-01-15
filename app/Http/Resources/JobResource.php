<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class JobResource.
 *
 * @property string id
 * @property string creator_id
 * @property string file_id
 * @property string worker_type
 * @property string worker_id
 * @property string bot_id
 */
class JobResource extends JsonResource
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
                    'link' => route('api.jobs.view', $this->id),
                ],
                'creator' => [
                    'id' => $this->creator_id,
                    'link' => route('api.users.view', $this->creator_id),
                ],
                'file' => [
                    'id' => $this->file_id,
                    'link' => route('api.files.view', $this->file_id),
                ],
                'worker' => $this->when(!is_null($this->worker_id), function () {
                    if ($this->worker_type == 'bots') {
                        return [
                            'id' => $this->worker_id,
                            'link' => route('api.bots.view', $this->worker_id),
                        ];
                    } elseif ($this->worker_type == 'clusters') {
                        return [
                            'id' => $this->worker_id,
                            'link' => route('api.clusters.view', $this->worker_id),
                        ];
                    } else {
                        return null;
                    }
                }, null),
                'bot' => $this->when(!is_null($this->bot_id), function () {
                    return [
                        'id' => $this->bot_id,
                        'link' => route('api.bots.view', $this->bot_id),
                    ];
                }, null),
            ],
        ];
    }

    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
