<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Webhook\Repository\AbstractPdkWebhooksRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;

final class MockPdkWebhooksRepository extends AbstractPdkWebhooksRepository
{
    private const DEFAULT_SUBSCRIPTIONS = [
        [
            'id'   => 1,
            'hook' => WebhookSubscription::SHOP_CARRIER_CONFIGURATION_UPDATED,
            'url'  => 'https://example.com',
        ],
        [
            'id'   => 2,
            'hook' => WebhookSubscription::SHOP_UPDATED,
            'url'  => 'https://example.com',
        ],
    ];

    /**
     * @var string
     */
    protected $hashedUrl = 'https://example.com/hook/1234567890abcdef';

    /**
     * @var array
     */
    protected $subscriptions;

    /**
     * @param  array                                                            $subscriptions
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface          $cache
     * @param  \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $subscriptionRepository
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(
        array                         $subscriptions = self::DEFAULT_SUBSCRIPTIONS,
        StorageDriverInterface        $cache,
        WebhookSubscriptionRepository $subscriptionRepository
    ) {
        parent::__construct($cache, $subscriptionRepository);

        $this->subscriptions = new WebhookSubscriptionCollection($subscriptions);
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
     * @param  \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection $key
     *
     * @return void
     */
    public function store(WebhookSubscriptionCollection $key): void
    {
        $this->subscriptions = $key;
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
