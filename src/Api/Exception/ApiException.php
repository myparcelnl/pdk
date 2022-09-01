<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Exception;

use Exception;
use MyParcelNL\Pdk\Api\Response\ClientResponseInterface;
use Throwable;

class ApiException extends Exception
{
    /**
     * @param  \MyParcelNL\Pdk\Api\Response\ClientResponseInterface $response
     * @param  int                                                  $code
     * @param  \Throwable|null                                      $previous
     */
    public function __construct(ClientResponseInterface $response, int $code = 0, Throwable $previous = null)
    {
        $body = json_decode($response->getBody(), true);

        parent::__construct(
            sprintf(
                'Request failed. Status code: %s. Errors: %s',
                $response->getStatusCode(),
                $body['message']
            ),
            $code,
            $previous
        );
    }
}
