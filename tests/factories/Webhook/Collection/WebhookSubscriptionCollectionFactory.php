<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Collection;

use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscriptionFactory;

/**
 * @template T of WebhookSubscriptionCollection
 * @method WebhookSubscriptionCollection make()
 */
final class WebhookSubscriptionCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return WebhookSubscriptionCollection::class;
    }

    protected function getModelFactory(): string
    {
        return WebhookSubscriptionFactory::class;
    }
}
