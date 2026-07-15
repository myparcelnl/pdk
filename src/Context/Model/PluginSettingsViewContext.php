<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\View\CarrierSettingsView;
use MyParcelNL\Pdk\Frontend\View\CheckoutSettingsView;
use MyParcelNL\Pdk\Frontend\View\CustomsSettingsView;
use MyParcelNL\Pdk\Frontend\View\LabelSettingsView;
use MyParcelNL\Pdk\Frontend\View\OrderSettingsView;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use Throwable;

/**
 * @property array{name: string, label: string, type: string}[]   $order
 * @property array{name: string, label: string, type: string}[]   $label
 * @property array{name: string, label: string, type: string}[]   $customs
 * @property array{name: string, label: string, type: string}[]   $checkout
 * @property array{name: string, label: string, type: string}[][] $carrier
 */
class PluginSettingsViewContext implements Arrayable
{
    private const ID_VIEW_MAP = [
        OrderSettings::ID    => OrderSettingsView::class,
        LabelSettings::ID    => LabelSettingsView::class,
        CustomsSettings::ID  => CustomsSettingsView::class,
        CheckoutSettings::ID => CheckoutSettingsView::class,
        CarrierSettings::ID  => CarrierSettingsView::class,
    ];

    private $views = [];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (! AccountSettings::getAccount()) {
            return;
        }

        // Do not show settings if there is no active proposition
        try {
            Pdk::get(PropositionService::class)->getPropositionConfig();
        } catch (\InvalidArgumentException $e) {
            Notifications::error(
                'Proposition not found',
                sprintf('Proposition with ID %d was not found, please contact support.', Pdk::get(PropositionService::class)->getActivePropositionId()),
                Notification::CATEGORY_GENERAL
            );
            return;
        }

        try {
            foreach (self::ID_VIEW_MAP as $id => $viewClass) {
                /** @var \MyParcelNL\Pdk\Frontend\View\AbstractSettingsView $view */
                $view = Pdk::get($viewClass);

                $this->views[$id] = $view->toArray();
            }
        } catch (Throwable $e) {
            // Never let a failing settings view prevent the page from rendering,
            // otherwise the account settings (including the api key form) would
            // not be shown at all.
            Logger::error('Failed to create settings views', ['exception' => $e]);

            Notifications::error(
                'Failed to load settings',
                $e->getMessage(),
                Notification::CATEGORY_GENERAL
            );

            $this->views = [];
        }
    }

    /**
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        return $this->views;
    }
}
