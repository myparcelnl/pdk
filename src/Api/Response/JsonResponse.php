<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Facade\Notifications;
use Symfony\Component\HttpFoundation\Response;

class JsonResponse extends Response
{
    /**
     * Factory method for fluent creation. Uses a Symfony-compatible signature
     * when Response::create() exists and normalizes content to the array payload
     * expected by this response class.
     *
     * @param  mixed $content
     * @param  int   $status
     * @param  array $headers
     *
     * @return static
     */
    public static function create($content = '', int $status = 200, array $headers = [])
    {
        if (is_array($content)) {
            $data = $content;
        } elseif (null === $content || '' === $content) {
            $data = [];
        } else {
            $data = ['content' => $content];
        }
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
