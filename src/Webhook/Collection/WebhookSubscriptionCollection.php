<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;

/**
 * @property \MyParcelNL\Pdk\Webhook\Model\WebhookSubscription[] $items
 */
class WebhookSubscriptionCollection extends Collection
{
    protected $cast = WebhookSubscription::class;

    /**
     * @return $this
     */
    public function addIds(Collection $ids): self
    {
        $this->each(function (WebhookSubscription $shipment, int $index) use ($ids) {
            $shipment->fill($ids->offsetGet($index) ?? []);

            return $shipment;
        });

        return $this;
    }
}
