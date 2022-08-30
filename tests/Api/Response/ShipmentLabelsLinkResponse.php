<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class ShipmentLabelsLinkResponse extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode([
                'data' => [
                    'pdfs' => ['url' => '/pdfs/label_hash'],
                ],
            ])
        );
    }
}
