<?php

namespace App\Policies;

use App\Models\Bot;
use App\Models\File;
use App\Models\Host;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;
    use UsesTokens;

    /**
     * @param User|Host $authed
     * @param File $file
     * @return bool
     * @throws AuthorizationException
     */
    public function view($authed, File $file): bool
    {
        $this->userNeedsScope($authed, 'files');

        if ($authed instanceof User) {
            return $authed->id === $file->uploader_id;
        }

        if ($authed instanceof Host) {
            $bots = $authed->bots()->with(['currentJob', 'currentJob.file'])->get();
            /** @var Bot $bot */
            foreach ($bots as $bot) {
                if (is_null($bot->currentJob)) continue;

                if ($bot->currentJob->file_id === $file->id) {
                    return true;
                }
            }
        }

        return false;
    }

}
