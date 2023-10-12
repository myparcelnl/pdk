<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PhysicalProperties
 * @method PhysicalProperties make()
 * @method $this withHeight(int $height)
 * @method $this withLength(int $length)
 * @method $this withManualWeight(int $manualWeight)
 * @method $this withInitialWeight(int $initialWeight)
 * @method $this withWidth(int $width)
 */
final class PhysicalPropertiesFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PhysicalProperties::class;
    }
}
