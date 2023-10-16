<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Api\Service\PdkActionsService;
use MyParcelNL\Pdk\Base\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ResetInterface;

final class MockPdkActionsService extends PdkActionsService implements ResetInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    private $calls;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * @param  string|\Symfony\Component\HttpFoundation\Request $action
     * @param  array                                            $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    public function execute($action, array $parameters = []): Response
    {
        $this->calls->push([
            'action'     => $action,
            'parameters' => $parameters,
        ]);

        return parent::execute($action, $parameters);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getCalls(): Collection
    {
        return $this->calls;
    }

    public function reset(): void
    {
        $this->calls = new Collection();
    }
}
