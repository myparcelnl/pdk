<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ActionInterface
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    public function handle(Request $request): Response;
}
