<?php

namespace App\ModelTraits;


use App\Models\OctoPrintAPIUser;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

trait HasOctoPrintUser
{
    public function octoPrintAPIUser(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(OctoPrintAPIUser::class, 'worker')
            ->ofMany([], function ($query) {
                /** @var Builder $query */
                $query->where('creator_id', '=', Auth::id());
            });
    }
}