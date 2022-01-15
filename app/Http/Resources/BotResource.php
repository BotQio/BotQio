<?php

namespace App\Http\Resources;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BotResource.
 *
 * @property string $id
 * @property string creator_id
 * @property string host_id
 * @property string cluster_id
 * @property string current_job_id
 */
class BotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->resource->attributesToArray(),
            'links' => [
                'self' => [
                    'id' => $this->id,
                    'link' => route('api.bots.view', $this->id),
                ],
                'creator' => [
                    'id' => $this->creator_id,
                    'link' => route('api.users.view', $this->creator_id),
                ],
                'host' => $this->when(!is_null($this->host_id), function () {
                    return [
                        'id' => $this->host_id,
                        'link' => route('api.hosts.view', $this->host_id),
                    ];
                }, null),
                'cluster' => [
                    'id' => $this->cluster_id,
                    'link' => route('api.clusters.view', $this->cluster_id),
                ],
                'current_job' => $this->when(!is_null($this->current_job_id), function () {
                    return [
                        'id' => $this->current_job_id,
                        'link' => route('api.jobs.view', $this->current_job_id),
                    ];
                }, null),
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
