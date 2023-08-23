<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class MockApiService extends AbstractApiService
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    private $mock;

    /**
     * @param  \MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter $clientAdapter
     */
    public function __construct(Guzzle7ClientAdapter $clientAdapter)
    {
        $mock   = new MockHandler();
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $clientAdapter->setClient($client);

        parent::__construct($clientAdapter);

        $this->mock = $mock;
    }

    /**
     * @param  \GuzzleHttp\Psr7\Response ...$responses
     *
     * @return void
     */
    public function enqueue(Response ...$responses): void
    {
        $this->getMock()
            ->append(...$responses);
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     * @noinspection PhpUnused
     */
    public function ensureLastRequest(): RequestInterface
    {
        $lastRequest = $this->getLastRequest();

        if (! $lastRequest) {
            throw new RuntimeException('No request was made.');
        }

        return $lastRequest;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return 'API';
    }

    /**
     * @return \Psr\Http\Message\RequestInterface|null
     */
    public function getLastRequest(): ?RequestInterface
    {
        return $this
            ->getMock()
            ->getLastRequest();
    }

    /**
     * @return null|array
     * @noinspection PhpUnused
     */
    public function getLastRequestBody(): ?array
    {
        $lastRequest = $this->getLastRequest();

        if (! $lastRequest) {
            return null;
        }

        return json_decode(
            $lastRequest->getBody()
                ->getContents(),
            true
        );
    }

    /**
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public function getMock(): MockHandler
    {
        return $this->mock;
    }
}
