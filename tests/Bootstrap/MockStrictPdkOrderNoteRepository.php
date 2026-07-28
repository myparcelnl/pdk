<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderNoteRepository;

/**
 * Order note repository that rejects orders without an identifier, like the platform
 * repositories do: WooCommerce and PrestaShop look the platform order up by id, and passing
 * null throws there. Use this double to prove PDK code never asks for the notes of an order
 * that only exists in memory.
 */
class MockStrictPdkOrderNoteRepository extends AbstractPdkOrderNoteRepository
{
    public function add(PdkOrderNote $note): void
    {
        // Not needed for these tests.
    }

    public function getFromOrder(PdkOrder $order): PdkOrderNoteCollection
    {
        if (null === $order->externalIdentifier) {
            throw new InvalidArgumentException('Invalid input');
        }

        return new PdkOrderNoteCollection();
    }

    public function update(PdkOrderNote $note): void
    {
        // Not needed for these tests.
    }
}
