<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Model;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;

/**
 * @template T of WebhookSubscription
 * @method WebhookSubscription make()
 * @method $this withHook(string $hook)
 * @method $this withId(int $id)
 * @method $this withUrl(string $url)
 */
final class WebhookSubscriptionFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return WebhookSubscription::class;
    }

    public function store(): ModelFactoryInterface
    {
        /** @var PdkWebhooksRepositoryInterface $repo */
        $repo = Pdk::get(PdkWebhooksRepositoryInterface::class);
        $repo->store(new WebhookSubscriptionCollection($this->make()));

        return $this;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withId($this->getNextId())
            ->withHook(WebhookSubscription::SHIPMENT_STATUS_CHANGE)
            ->withUrl('API/webhook/1234567890');
    }
}
