<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Bootstrap;

use MyParcelNL\Pdk\Api\Contract\ApiResponseInterface;
use MyParcelNL\Pdk\Api\Request\RequestInterface;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use Symfony\Component\HttpFoundation\Request;

final class BehatApiService extends MyParcelApiService
{
    /**
     * @param  \MyParcelNL\Pdk\Tests\Integration\Bootstrap\BehatClientAdapter $clientAdapter
     */
    public function __construct(BehatClientAdapter $clientAdapter)
    {
        parent::__construct($clientAdapter);
    }

    /**
     * @param  \MyParcelNL\Pdk\Api\Request\RequestInterface $request
     * @param  string                                       $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ApiResponseInterface
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     */
    public function doRequest(
        RequestInterface $request,
        string           $responseClass = ApiResponseWithBody::class
    ): ApiResponseInterface {
        $symfonyRequest = $this->convertToSymfonyRequest($request);

        $symfonyRequest->overrideGlobals();

        return parent::doRequest($request, $responseClass);
    }

    /**
     * @param  \MyParcelNL\Pdk\Api\Request\RequestInterface $request
     *
     * @return string
     */
    protected function buildUri(RequestInterface $request): string
    {
        $uri = parent::buildUri($request);

        if ('PDK' === $request->getPath()) {
            $uri = str_replace($this->getBaseUrl(), '', $uri);
        }

        return $uri;
    }

    /**
     * @param  \MyParcelNL\Pdk\Api\Request\RequestInterface $request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function convertToSymfonyRequest(RequestInterface $request): Request
    {
        $server = [];

        foreach ($request->getHeaders() as $key => $value) {
            $server[sprintf('HTTP_%s', strtoupper($key))] = $value;
        }

        return Request::create(
            $request->getPath(),
            $request->getMethod(),
            $this->parseQueryString($request->getQueryString()),
            [],
            [],
            $server,
            $request->getBody()
        );
    }

    /**
     * @param  string $queryString
     *
     * @return array
     */
    protected function parseQueryString(string $queryString): array
    {
        return array_reduce(
            explode('&', $queryString),
            static function (array $carry, string $item): array {
                [$key, $value] = explode('=', $item);

                $carry[$key] = $value;

                return $carry;
            },
            []
        );
    }
}
