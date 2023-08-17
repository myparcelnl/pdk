<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PackageType
 * @method PackageType make()
 * @method $this withId(int $id)
 * @method $this withName(string $name)
 */
final class PackageTypeFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PackageType::class;
    }
}
