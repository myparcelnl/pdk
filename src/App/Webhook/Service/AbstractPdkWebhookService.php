<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Service;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface;

abstract class AbstractPdkWebhookService implements PdkWebhookServiceInterface
{
    public function createUrl(): string
    {
        return sprintf('%s/%s', $this->getBaseUrl(), $this->generateHash());
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function generateHash(): string
    {
        return md5(random_bytes(32));
    }
}
