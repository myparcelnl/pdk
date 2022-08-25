<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Action\PdkActionManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intercepts executions and just keeps track of which parameters have been passed.
 */
class MockPdkActionManager extends PdkActionManager
{
    /**
     * @var \MyParcelNL\Sdk\src\Support\Collection
     */
    private $requests;

    public function __construct()
    {
        $this->requests = new Collection();
    }

    public function execute(array $parameters): Response
    {
        $this->requests->push($parameters);

        return new Response();
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }
}
