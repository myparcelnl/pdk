<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use MyParcelNL\Pdk\Base\Http\ResponseCodes;
use Psr\Http\Message\StreamInterface;

class JsonResponse extends Response
{
    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody(): StreamInterface
    {
        $content = $this->getContent();
        $body    = null;

        if (! empty($content)) {
            $body = json_encode($content);
        }

        return Utils::streamFor($body);
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return [];
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
        return ResponseCodes::HTTP_OK;
    }
}
