<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Collection;

use MyParcelNL\Pdk\Notification\Model\NotificationFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of NotificationCollection
 * @method NotificationCollection make()
 */
final class NotificationCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return NotificationCollection::class;
    }

    protected function getModelFactory(): string
    {
        return NotificationFactory::class;
    }
}
