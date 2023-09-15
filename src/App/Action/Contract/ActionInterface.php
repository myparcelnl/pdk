<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Contract;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ActionInterface
{
    /**
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    public function handle(Request $request): Response;
}
