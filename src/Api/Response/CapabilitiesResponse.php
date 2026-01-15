<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response for capabilities API requests
 */
class CapabilitiesResponse extends ApiResponse
{
    /**
     * @var null|string
     */
    private $body;

    /**
     * @var null|int
     */
    private $statusCode;

    public function __construct(ClientResponseInterface $response)
    {
        parent::__construct($response);
        $this->body       = $response->getBody();
        $this->statusCode = $response->getStatusCode();
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getSymfonyResponse(): Response
    {
        return new Response($this->body, $this->statusCode, ['Content-Type' => 'application/json']);
    }
}
