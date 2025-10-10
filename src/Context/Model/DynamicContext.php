<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\View\PrintOptionsView;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;

/**
 * @property Account           $account
 * @property CarrierCollection $carriers
 * @property Settings          $pluginSettings
 * @property Shop              $shop
 */
class DynamicContext extends Model
{
    public $attributes = [
        'account'          => null,
        'carriers'         => null,
        'pluginSettings'   => null,
        'printOptionsView' => null,
        'shop'             => null,
    ];

    protected $casts      = [
        'account'          => Account::class,
        'carriers'         => CarrierCollection::class,
        'pluginSettings'   => Settings::class,
        'printOptionsView' => PrintOptionsView::class,
        'shop'             => Shop::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
        $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);

        $this->attributes['pluginSettings'] = $settingsRepository->all();

        if (\MyParcelNL\Pdk\Facade\Settings::get(LabelSettings::PROMPT, LabelSettings::ID)) {
            $this->attributes['printOptionsView'] = Pdk::get(PrintOptionsView::class);
        }

        $this->attributes['account']  = AccountSettings::getAccount();
        $this->attributes['carriers'] = FrontendData::carrierCollectionToLegacyFormat(
            AccountSettings::getCarriers()
        );
        $this->attributes['shop']     = AccountSettings::getShop();
    }
}
