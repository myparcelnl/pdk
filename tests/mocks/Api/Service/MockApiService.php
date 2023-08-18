<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Api\Adapter\Guzzle7ClientAdapter;
use MyParcelNL\Pdk\Contract\MockServiceInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * @property \MyParcelNL\Pdk\Api\Adapter\Guzzle7ClientAdapter $clientAdapter
 */
final class MockApiService extends AbstractApiService implements MockServiceInterface
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    private $mock;

    /**
     * @param  \MyParcelNL\Pdk\Api\Adapter\Guzzle7ClientAdapter $clientAdapter
     */
    public function __construct(Guzzle7ClientAdapter $clientAdapter)
    {
        parent::__construct($clientAdapter);

        $this->createNewMockClient();
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
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public function getMock(): MockHandler
    {
        return $this->mock;
    }

    public function reset(): void
    {
        $this->createNewMockClient();
    }

    /**
     * @return void
     */
    private function createNewMockClient(): void
    {
        $this->mock = new MockHandler();

        $this->clientAdapter->setClient(
            new Client([
                'handler' => HandlerStack::create($this->mock),
            ])
        );
    }
}
