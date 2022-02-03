<?php

namespace App\Actions;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\BotStatusConflict;
use App\Exceptions\JobAssignmentFailed;
use App\Exceptions\JobStatusConflict;
use App\Models\Bot;
use App\Models\Job;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Support\Facades\Log;
use Spatie\QueueableAction\QueueableAction;

class FindJobForBot
{
    use QueueableAction;

    /**
     * @var AssignJobToBot
     */
    private $assignJobToBot;

    /**
     * @param AssignJobToBot $assignJobToBot
     */
    public function __construct(AssignJobToBot $assignJobToBot)
    {
        $this->assignJobToBot = $assignJobToBot;
    }

    /**
     * Execute the action.
     *
     * @param Bot|ModelIdentifier $bot
     */
    public function execute($bot)
    {
        if($bot instanceof ModelIdentifier) {
            $bot = Bot::findOrFail($bot->id);
        }

        Log::info("Finding job for bot: {$bot->id}");

        if ($bot->status != BotStatusEnum::IDLE) {
            Log::info("Bot status is not idle, doing nothing");
            return;
        }

        $this->assignJobsFromModel($bot, $bot);

        if ($bot->status != BotStatusEnum::IDLE) {
            Log::info("Bot status is not idle, probably assigned a job");
            return;
        }

        $cluster = $bot->cluster;
        if ($cluster == null) {
            Log::info("Cluster is null, leaving");
            return;
        }

        $this->assignJobsFromModel($bot, $cluster);

        Log::info("Made it to the end!");
    }

    /**
     * @param Bot $bot
     * @param $model
     */
    private function assignJobsFromModel(Bot $bot, $model): void
    {
        Job::query()
            ->where('worker_id', $model->id)
            ->where('worker_type', $model->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->each(function ($job) use ($bot) {
                return $this->attemptAssignment($bot, $job);
            });
    }

    private function attemptAssignment(Bot $bot, Job $job)
    {
        try {
            Log::info("Trying to assign job {$job->id} to bot {$bot->id}");
            $this->assignJobToBot->execute($bot, $job);
            Log::info("I think we did it!");

            return false;
        } catch (BotStatusConflict $e) {
            Log::error("BotStatusConflict: {$e->getMessage()}");
            return false;
        } catch (BotIsNotValidWorker $e) {
            Log::error("BotIsNotValidWorker: {$e->getMessage()}");
            return true;
        } catch (JobStatusConflict $e) {
            Log::error("JobStatusConflict: {$e->getMessage()}");
            return true;
        } catch (JobAssignmentFailed $e) {
            Log::error("JobAssignmentFailed: {$e->getMessage()}");
            // Something failed while trying to assign the job Refresh the bot,
            // just in case it has an updated status, but also keep searching for jobs
            $bot->refresh();
            return true;
        }
    }
}
