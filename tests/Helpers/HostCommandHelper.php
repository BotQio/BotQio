<?php

namespace Tests\Helpers;


trait HostCommandHelper
{
    public function command($command, $data = null)
    {
        $toPost = ['command' => $command];
        if (!is_null($data)) {
            $toPost['data'] = $data;
        }

        return $this->postJson('/host', $toPost);
    }
}