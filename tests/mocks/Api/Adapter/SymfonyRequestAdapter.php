<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Adapter;

use Symfony\Component\HttpFoundation\Request;

final class SymfonyRequestAdapter
{
    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function fromParts(string $httpMethod, string $uri, array $options): Request
    {
        $symfonyRequest = Request::create(
            $uri,
            $httpMethod,
            [],
            [],
            [],
            [],
            $options['body'] ?? null
        );

        foreach ($options['headers'] ?? [] as $key => $value) {
            $symfonyRequest->headers->set($key, $value);
        }

        return $symfonyRequest;
    }
}
