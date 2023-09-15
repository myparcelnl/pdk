<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Facade\Notifications;
use Symfony\Component\HttpFoundation\Response;

class JsonResponse extends Response
{
    public function __construct(array $data = [], int $status = 200, array $headers = [])
    {
        if (Notifications::isNotEmpty()) {
            $data['notifications'] = Notifications::all()
                ->toArrayWithoutNull();
        }

        parent::__construct(
            json_encode(['data' => $data], JSON_THROW_ON_ERROR),
            $status,
            ['Content-Type' => 'application/json'] + $headers
        );
    }
}
