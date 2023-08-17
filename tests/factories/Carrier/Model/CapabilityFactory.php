<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Capability
 * @method Capability make()
 * @method $this withEnum(array $enum)
 * @method $this withMaxLength(int $maxLength)
 * @method $this withMaximum(int $maximum)
 * @method $this withMinLength(int $minLength)
 * @method $this withMinimum(int $minimum)
 * @method $this withType(string $type)
 */
final class CapabilityFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Capability::class;
    }
}
