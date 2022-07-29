<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class ShipmentLabelsLinkV2Response extends Response
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode([
                'data' => [
                    'pdf' => ['url' => '/pdfs/label_hash'],
                ],
            ])
        );
    }
}
