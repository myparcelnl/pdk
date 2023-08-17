<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

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
}
