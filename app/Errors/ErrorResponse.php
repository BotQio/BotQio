<?php

namespace App\Errors;

use Illuminate\Http\JsonResponse;

class ErrorResponse extends JsonResponse
{
    private $code;
    private $message;
    private $httpStatusCode;

    public function __construct($code, $message, $httpStatusCode)
    {
        $this->code = $code;
        $this->message = $message;
        $this->httpStatusCode = $httpStatusCode;

        parent::__construct(
            $this->toArray(),
            $httpStatusCode
        );
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'ok' => false,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
