<?php

declare(strict_types=1);

namespace ftab\Dogecoin\Exceptions;

use ftab\Dogecoin\Responses\Response;

class BadRemoteCallException extends ClientException
{
    /**
     * Response object.
     *
     * @var \ftab\Dogecoin\Responses\Response
     */
    protected $response;

    /**
     * Constructs new bad remote call exception.
     *
     * @param \ftab\Dogecoin\Responses\Response $response
     *
     * @return void
     */
    public function __construct(Response $response)
    {
        $this->response = $response;

        $error = $response->error();
        parent::__construct($error['message'], $error['code']);
    }

    /**
     * Gets response object.
     *
     * @return \ftab\Dogecoin\Responses\Response
     */
    public function getResponse() : Response
    {
        return $this->response;
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    protected function getConstructorParameters() : array
    {
        return [
            $this->getResponse(),
        ];
    }
}
