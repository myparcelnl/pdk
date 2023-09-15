<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Adapter;

use Symfony\Component\HttpFoundation\Request;

final class SymfonyRequestAdapter
{
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
