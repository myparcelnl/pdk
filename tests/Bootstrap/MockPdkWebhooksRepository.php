<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Webhook\Repository\AbstractPdkWebhooksRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;
use Symfony\Contracts\Service\ResetInterface;

final class MockPdkWebhooksRepository extends AbstractPdkWebhooksRepository implements ResetInterface
{
    /**
     * @var null|string
     */
    protected $hashedUrl;

    /**
     * @var WebhookSubscriptionCollection
     */
    protected $subscriptions;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                $storage
     * @param  \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $subscriptionRepository
     */
    public function __construct(StorageInterface $storage, WebhookSubscriptionRepository $subscriptionRepository)
    {
        parent::__construct($storage, $subscriptionRepository);

        $this->reset();
    }

    /**
     * @return \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection
     */
    public function getAll(): WebhookSubscriptionCollection
    {
        return $this->subscriptions;
    }

    /**
     * @return null|string
     */
    public function getHashedUrl(): ?string
    {
        return $this->hashedUrl;
    }

    /**
     * @param  string $hook
     *
     * @return void
     */
    public function remove(string $hook): void
    {
        $this->subscriptions = $this->subscriptions->where('hook', '!=', $hook);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->subscriptions = new WebhookSubscriptionCollection();
    }

    /**
     * @param  \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection $subscriptions
     *
     * @return void
     */
    public function store(WebhookSubscriptionCollection $subscriptions): void
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * @param  string $url
     *
     * @return void
     */
    public function storeHashedUrl(string $url): void
    {
        $this->hashedUrl = $url;
    }
}
