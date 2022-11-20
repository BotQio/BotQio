<?php

namespace App\Http\Controllers;

use App\Enums\JobStatusEnum;
use App\Models\Bot;
use App\Models\Cluster;
use App\Models\File;
use App\Models\Job;
use App\Models\OctoPrintAPIUser;
use App\Rules\Extension;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OctoPrintController extends Controller
{
    public function version(): JsonResponse
    {
        return response()->json([
            'api' => '0.1',
            'server' => '1.0.0',
            'text' => 'OctoPrint BotQio interface'
        ]);
    }

    public function upload(Request $request)
    {
        if(! $request->has('path')) {
            return response("No path given", 400);
        }
        if(! $request->files->has('file')) {
            return response("No file uploaded", 400);
        }
        $uploadedFile = $request->file('file');
        if(! $uploadedFile->isValid()) {
            return response($uploadedFile->getErrorMessage(), 500);
        }

        $shouldQueue = $request->input('print', true);
        $basePath = $request->input('path');

        $validator = Validator::make([
            'file' => $uploadedFile
        ], [
            'file' => [new Extension(['gcode', 'stl'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => "Unsupported file type: {$uploadedFile->getClientOriginalExtension()}"
            ], 422);
        }

        /** @var OctoPrintAPIUser $octoPrintUser */
        $octoPrintUser = $request->user();
        $file = File::fromUploadedFile($uploadedFile, $octoPrintUser->creator);

        $jobName = $file->name;
        if(! empty($basePath)) {
            $jobName = "$basePath/$jobName";
        }

        if($shouldQueue) {
            $job = new Job([
                'name' => $jobName,
                'status' => JobStatusEnum::QUEUED,
                'creator_id' => $octoPrintUser->creator,
                'file_id' => $file->id,
            ]);

            $job->worker()->associate($octoPrintUser->worker);
            $job->save();
        } else {
            response("F YOU TOO", 500);  // TODO shouldQueue never seems to be false
        }

        return response("", ); // TODO Return an actual useful response?
    }
}
