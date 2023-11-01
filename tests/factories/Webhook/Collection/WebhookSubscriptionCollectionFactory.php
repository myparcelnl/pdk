<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Collection;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;
use MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface;
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

    public function store(): CollectionFactoryInterface
    {
        /** @var PdkWebhooksRepositoryInterface $repo */
        $repo = Pdk::get(PdkWebhooksRepositoryInterface::class);
        $repo->store($this->make());

        return $this;
    }

    protected function getModelFactory(): string
    {
        return WebhookSubscriptionFactory::class;
    }
}
