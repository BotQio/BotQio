<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobCollection;
use App\Http\Resources\JobResource;
use App\Models\Host;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        /** @var User|Host $user */
        $user = Auth::user();

        $jobs = $user->jobs()->paginate();

        return new JobCollection($jobs);
    }

    public function show(Job $job)
    {
        return new JobResource($job);
    }
}
