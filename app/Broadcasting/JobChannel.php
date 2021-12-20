<?php

namespace App\Broadcasting;

use App\Models\Host;
use App\Models\Job;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class JobChannel
{
    public function join(Authenticatable $authenticatable, string $id)
    {
        /** @var Job $job */
        $job = Job::find($id);
        if (is_null($job)) {
            return false;
        }

        if ($authenticatable instanceof User) {
            return $authenticatable->id === $job->creator_id;
        } else if ($authenticatable instanceof Host) {
            if (is_null($job->bot_id)) {
                return false;
            }

            $bot = $job->bot;
            return $authenticatable->id == $bot->host_id;
        }

        return false;
    }
}
