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

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     * @param  \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface  $api
     */
    public function __construct(StorageInterface $storage, ApiServiceInterface $api)
    {
        parent::__construct($storage);

        $this->api = $api;
    }
}
