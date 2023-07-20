<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;

class ApiRepository extends Repository
{
    /**
     * @var \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface
     */
    protected $api;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface $cache
     * @param  \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface       $api
     */
    public function __construct(CacheStorageInterface $cache, ApiServiceInterface $api)
    {
        parent::__construct($cache);

        $this->api = $api;
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return 'api:';
    }
}
