<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ShipmentsResponse extends JsonResponse
{
    private const DEFAULT_IDS = [1];

    /**
     * @var array|int[]
     */
    private $ids;

    public function __construct(
        array  $ids = self::DEFAULT_IDS,
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->ids = $ids;
    }

    /**
     * @return array[]
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'shipments' => array_map(static function (int $id) {
                    return ['id' => $id];
                }, $this->ids),
            ],
        ];
    }
}
