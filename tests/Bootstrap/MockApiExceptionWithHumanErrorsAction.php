<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Response\ClientResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Throws an api exception in the shape the MyParcel api uses for validation errors, where every
 * error is keyed by its code and the readable messages live in a "human" array.
 */
class MockApiExceptionWithHumanErrorsAction implements ActionInterface
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
            'message'    => 'The given data was invalid.',
            'errors'     => [
                [
                    3212 => [
                        'fields' => ['recipient.street'],
                        'human'  => ['Street is required.', 'House number is required.'],
                    ],
                ],
            ],
            'request_id' => '67890',
        ];

        $response = new ClientResponse(json_encode($body), Response::HTTP_BAD_REQUEST);

        throw new ApiException($response);
    }
}
