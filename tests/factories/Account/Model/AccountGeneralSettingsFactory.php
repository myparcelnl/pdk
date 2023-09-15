<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of AccountGeneralSettings
 * @method AccountGeneralSettings make()
 * @method $this withHasCarrierContract(bool $hasCarrierContract)
 * @method $this withIsTest(bool $isTest)
 * @method $this withOrderMode(bool $orderMode)
 */
final class AccountGeneralSettingsFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return AccountGeneralSettings::class;
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\AccountGeneralSettings $model
     */
    protected function save(Model $model): void
    {
        factory(Account::class)
            ->withGeneralSettings($model)
            ->store();
    }
}
