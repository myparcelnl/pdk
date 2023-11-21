<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Contract;

use Symfony\Component\HttpFoundation\Response;

interface PdkActionsServiceInterface
{
    /**
     * @param  string|\Symfony\Component\HttpFoundation\Request $action
     * @param  array                                            $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    public function execute($action, array $parameters = []): Response;

    /**
     * @param  string|\Symfony\Component\HttpFoundation\Request $action
     * @param  array                                            $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    public function executeAutomatic($action, array $parameters = []): Response;

    /**
     * @param  string $context
     *
     * @return $this
     */
    public function setContext(string $context): self;
}
