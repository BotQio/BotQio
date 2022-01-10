<?php

namespace App\Http\HostCommands;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InfoCommand
{
    use HostCommandTrait;

    protected $ignoreHostAuth = true;

    /**
     * @param $data Collection
     * @return JsonResponse
     */
    public function __invoke(Collection $data): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'websocket' => [
                    'url' => $this->getWebsocketURL(),
                    'auth' => url('/broadcasting/auth'),
                ],
            ],
        ], Response::HTTP_ACCEPTED,
            [],
            JSON_UNESCAPED_SLASHES);
    }

    private function getWebsocketURL(): string
    {
        $url = config('app.url');
        $url = trim(Str::replaceFirst('http', 'ws', $url), '/');
        return "{$url}/ws/app/BotQio-key";
    }
}
