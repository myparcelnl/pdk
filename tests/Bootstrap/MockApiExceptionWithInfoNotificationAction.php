<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Response\ClientResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a notification that does not report the failure before it throws.
 */
class MockApiExceptionWithInfoNotificationAction implements ActionInterface
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     */
    public function handle(Request $request): Response
    {
        Notifications::info('Exporting order', null, Notification::CATEGORY_ACTION);

        $response = new ClientResponse(
            json_encode(['message' => 'boom', 'errors' => [], 'request_id' => '12345']),
            Response::HTTP_BAD_REQUEST
        );

        throw new ApiException($response);
    }
}
