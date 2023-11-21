<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Collection;

use MyParcelNL\Pdk\Audit\Model\AuditFactory;
use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of NotificationCollection
 * @method NotificationCollection make()
 */
final class AuditCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return AuditCollection::class;
    }

    protected function getModelFactory(): string
    {
        return AuditFactory::class;
    }
}
