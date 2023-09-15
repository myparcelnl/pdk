<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Contract;

use Symfony\Component\HttpFoundation\Response;

interface PdkApiInterface
{
    /**
     * Call an endpoint. You must pass a context to the endpoint. This context is used to determine which
     * actions are available to the endpoint.
     *
     * @param  string|\Symfony\Component\HttpFoundation\Request $input
     *
     * @see \MyParcelNL\Pdk\App\Api\PdkEndpoint::CONTEXT_FRONTEND
     * @see \MyParcelNL\Pdk\App\Api\PdkEndpoint::CONTEXT_BACKEND
     */
    public function call($input, string $context): Response;
}

