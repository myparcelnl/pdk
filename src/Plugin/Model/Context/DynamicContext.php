<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Admin\View\PrintOptionsView;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;

/**
 * @property \MyParcelNL\Pdk\Account\Model\Account   $account
 * @property \MyParcelNL\Pdk\Settings\Model\Settings $pluginSettings
 * @property PrintOptionsView                        $printOptions
 */
class DynamicContext extends Model
{
    public    $attributes = [
        'account'          => null,
        'pluginSettings'   => null,
        'printOptionsView' => null,
    ];

    protected $casts      = [
        'account'          => Account::class,
        'pluginSettings'   => Settings::class,
        'printOptionsView' => PrintOptionsView::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        /** @var SettingsRepositoryInterface $settingsRepository */
        $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);

        $this->attributes['pluginSettings'] = $settingsRepository->all();

        if (\MyParcelNL\Pdk\Facade\Settings::get(LabelSettings::PROMPT, LabelSettings::ID)) {
            $this->attributes['printOptionsView'] = Pdk::get(PrintOptionsView::class);
        }

        /** @var \MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface $accountRepository */
        $accountRepository = Pdk::get(AccountRepositoryInterface::class);

        $this->attributes['account'] = $accountRepository->getAccount();
    }
}
