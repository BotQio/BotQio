<?php

namespace App\Http\HostCommands;

use App\Enums\HostRequestStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\Exceptions\HostRequestAlreadyDeleted;
use App\Exceptions\OauthHostClientNotSetup;
use App\Exceptions\OauthHostKeysMissing;
use App\Exceptions\PersonalAccessClientMissing;
use App\Models\HostRequest;
use App\Http\Resources\HostResource;
use Exception;
use Illuminate\Support\Collection;

class ConvertRequestToHostCommand
{
    use HostCommandTrait;

    protected $ignoreHostAuth = true;

    /**
     * @param $data Collection
     * @return ErrorResponse|HostResource
     */
    public function __invoke($data)
    {
        /** @var HostRequest $host_request */
        $host_request = HostRequest::find($data->get('id'));

        if ($host_request == null) {
            return HostErrors::hostRequestNotFound();
        }

        if ($host_request->status !== HostRequestStatusEnum::CLAIMED) {
            return HostErrors::hostRequestIsNotClaimed();
        }

        try {
            $host = $host_request->toHost();

            $accessToken = $host->createHostToken();

            return new HostResource($host, $accessToken);
        } catch (OauthHostKeysMissing $e) {
            report($e);

            return HostErrors::oauthHostKeysMissing();
        } catch (HostRequestAlreadyDeleted $e) {
            return HostErrors::hostRequestNotFound();
        } catch (PersonalAccessClientMissing $e) {
            return HostErrors::oauthNoPersonalAccessToken();
        } catch (Exception $e) {
            report($e);

            return HostErrors::unknownError();
        }
    }
}
