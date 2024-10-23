<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Response\ClientResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MockApiExceptionAction implements ActionInterface
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     */
    public function handle(Request $request): Response
    {
        $body = [
            'message'    => 'boom',
            'errors'     => [
                [
                    'code'    => 24920,
                    'message' => 'Something went wrong',
                ],
                [
                    'code'    => 74892,
                    'message' => 'Something else also went wrong',
                ],
            ],
            'request_id' => '12345',
        ];

        $response = new ClientResponse(json_encode($body), Response::HTTP_BAD_REQUEST);

        throw new ApiException($response);
    }
}
