<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

abstract class AbstractMockResponse extends Response
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
}
