<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests\JobFileCreationRequest;
use Illuminate\Support\Facades\Auth;

class JobFileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(App\Models\File $file)
    {
        return view('job.create.file', [
            'file' => $file,
            'bots' => App\Models\Bot::mine()->get(),
            'clusters' => App\Models\Cluster::mine()->get(),
        ]);
    }

    public function store(App\Models\File $file, JobFileCreationRequest $request)
    {
        /** @var App\Models\Job $job */
        $job = new App\Models\Job([
            'name' => $request->get('job_name'),
            'status' => App\Enums\JobStatusEnum::QUEUED,
            'creator_id' => Auth::id(),
            'file_id' => $file->id,
        ]);

        $worker = $request->get('bot_cluster');

        $job->worker()->associate($worker);
        $job->save();

        return redirect()->route('jobs.show', $job);
    }
}
