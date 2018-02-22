<?php


namespace App;


class HostManager
{
    protected $host;

    /**
     * @param Host $host
     */
    public function setHost(Host $host)
    {
        $this->host = $host;
    }

    /**
     * @return Host
     */
    public function getHost()
    {
        return $this->host;
    }
}