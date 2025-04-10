<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Debug;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class DownloadLogsEndpointRequest extends AbstractEndpointRequest
{
    public function getHeaders(): array
    {
        return [
            'Accept' => 'application/zip',
        ];
    }

    public function getMethod(): string
    {
        return HttpRequest::METHOD_POST;
    }
}
