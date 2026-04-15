<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Facade\Notifications;
use Symfony\Component\HttpFoundation\Response;

class JsonResponse extends Response
{
    /**
     * Factory method for fluent creation. Restores compatibility with Symfony 6+
     * which removed Response::create().
     *
     * @param  array $data
     * @param  int   $status
     * @param  array $headers
     *
     * @return static
     */
    public static function create(array $data = [], int $status = 200, array $headers = []): self
    {
        return new static($data, $status, $headers);
    }

    /**
     * @param  array $data
     * @param  int   $status
     * @param  array $headers
     */
    public function __construct(array $data = [], int $status = 200, array $headers = [])
    {
        if (Notifications::isNotEmpty()) {
            $data['notifications'] = Notifications::all()
                ->toArrayWithoutNull();
        }

        parent::__construct(json_encode(['data' => $data]), $status, ['Content-Type' => 'application/json'] + $headers);
    }
}
