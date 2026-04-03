<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\SdkApi\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

/**
 * Base class for mocked responses returned to openapi-generator SDK clients.
 *
 * Unlike ExampleJsonResponse (legacy API), SDK client responses are plain JSON
 * with no 'data.{property}' envelope — the response body is the direct JSON
 * object the API returns and the ObjectSerializer deserialises.
 *
 * Subclasses implement getContent() to return the endpoint-specific payload.
 */
abstract class SdkJsonResponse extends Response
{
    /**
     * @var null|array
     */
    protected $responseContent;

    /**
     * @param  null|array  $responseContent  Override the default response body
     * @param  int         $status
     * @param  array       $headers
     * @param  null        $body
     * @param  string      $version
     * @param  string|null $reason
     */
    public function __construct(
        ?array $responseContent = null,
        int    $status = 200,
        array  $headers = ['Content-Type' => 'application/json'],
        $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->responseContent = $responseContent;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(json_encode($this->getContent()));
    }

    /**
     * Return the full JSON payload this response will serve.
     * The shape must match what the corresponding openapi-generated model expects.
     *
     * @return array
     */
    abstract protected function getContent(): array;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\SdkApi\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Base class for mocked responses returned to openapi-generator SDK clients.
 *
 * Unlike ExampleJsonResponse (legacy API), SDK client responses are plain JSON
 * with no 'data.{property}' envelope — the response body is the direct JSON
 * object the API returns and the ObjectSerializer deserialises.
 *
 * Subclasses implement getContent() to return the endpoint-specific payload.
 */
abstract class SdkJsonResponse extends Response
{
    /**
     * @var null|array
     */
    protected $responseContent;

    /**
     * @param  null|array  $responseContent  Override the default response body
     * @param  int         $status
     * @param  array       $headers
     * @param  null        $body
     * @param  string      $version
     * @param  string|null $reason
     */
    public function __construct(
        ?array $responseContent = null,
        int    $status = 200,
        array  $headers = [],
        $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->responseContent = $responseContent;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(json_encode($this->getContent()));
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return SymfonyResponse::HTTP_OK;
    }

    /**
     * Return the full JSON payload this response will serve.
     * The shape must match what the corresponding openapi-generated model expects.
     *
     * @return array
     */
    abstract protected function getContent(): array;
}
