<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class ApiRepository extends Repository
{
    /**
     * @var \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface
     */
    protected $api;

    public function __construct(StorageInterface $storage, ApiServiceInterface $api)
    {
        parent::__construct($storage);

        $this->api = $api;
    }

    protected function getKeyPrefix(): string
    {
        return 'api:';
    }
}
