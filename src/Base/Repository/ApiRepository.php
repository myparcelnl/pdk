<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Storage\StorageInterface;

class ApiRepository extends Repository
{
    /**
     * @var \MyParcelNL\Pdk\Api\Service\ApiServiceInterface
     */
    protected $api;

    /**
     * @param  \MyParcelNL\Pdk\Storage\StorageInterface        $storage
     * @param  \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api
     */
    public function __construct(StorageInterface $storage, ApiServiceInterface $api)
    {
        parent::__construct($storage);

        $this->api = $api;
    }
}
