<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Plugin\Service\CheckoutServiceInterface;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CheckoutSettingsView extends AbstractSettingsView
{
    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function getElements(): FormElementCollection
    {
        $elements = [

            new InteractiveElement(CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS, Components::INPUT_TOGGLE),
            new InteractiveElement(
                CheckoutSettings::PRICE_TYPE,
                Components::INPUT_SELECT,
                [
                    'options' => $this->toSelectOptions(
                        [
                            CheckoutSettings::PRICE_TYPE_INCLUDED => 'settings_checkout_price_type_option_included',
                            CheckoutSettings::PRICE_TYPE_EXCLUDED => 'settings_checkout_price_type_option_excluded',
                        ]
                    ),
                ]
            ),

            new InteractiveElement(CheckoutSettings::ENABLE_DELIVERY_OPTIONS, Components::INPUT_TOGGLE),
            new InteractiveElement(CheckoutSettings::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK, Components::INPUT_TOGGLE),
            new InteractiveElement(CheckoutSettings::DELIVERY_OPTIONS_HEADER, Components::INPUT_TEXT),
        ];

        if (Pdk::has(CheckoutServiceInterface::class)) {
            /** @var \MyParcelNL\Pdk\Plugin\Service\CheckoutServiceInterface $deliveryOptionsService */
            $checkoutService = Pdk::get(CheckoutServiceInterface::class);

            $elements[] = new InteractiveElement(
                CheckoutSettings::DELIVERY_OPTIONS_POSITION,
                Components::INPUT_SELECT,
                [
                    'options' => $this->toSelectOptions($checkoutService->getPositions()),
                ]
            );
        }

        return new FormElementCollection($elements);
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return CheckoutSettings::ID;
    }
}
