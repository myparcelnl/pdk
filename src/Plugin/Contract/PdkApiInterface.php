<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

use Symfony\Component\HttpFoundation\Response;

interface PdkApiInterface
{
    /**
     * Call an endpoint. You must pass a context to the endpoint. This context is used to determine which
     * actions are available to the endpoint.
     *
     * @param  string|\Symfony\Component\HttpFoundation\Request $input
     * @param  string                                           $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see \MyParcelNL\Pdk\Plugin\Api\PdkEndpoint::CONTEXT_FRONTEND
     * @see \MyParcelNL\Pdk\Plugin\Api\PdkEndpoint::CONTEXT_BACKEND
     */
    public function call($input, string $context): Response;
}

