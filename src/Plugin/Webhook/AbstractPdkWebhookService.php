<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Webhook;

abstract class AbstractPdkWebhookService implements PdkWebhookServiceInterface
{
    /**
     * @return string
     */
    public function createUrl(): string
    {
        return sprintf('%s/%s', $this->getBaseUrl(), $this->generateHash());
    }

    /**
     * @return string
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function generateHash(): string
    {
        return md5(random_bytes(32));
    }
}
