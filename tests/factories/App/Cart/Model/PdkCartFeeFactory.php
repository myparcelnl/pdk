<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PdkCartFee
 * @method PdkCartFee make()
 * @method $this withAmount(float $amount)
 * @method $this withId(string $id)
 * @method $this withTranslation(string $translation)
 */
final class PdkCartFeeFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PdkCartFee::class;
    }
}
