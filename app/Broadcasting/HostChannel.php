<?php

namespace App\Broadcasting;

use App\Models\Host;

class HostChannel
{
    public function join(Host $host, string $id): bool
    {
        return $host->id === $id;
    }
}
