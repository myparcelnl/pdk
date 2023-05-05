<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Plugin\Contract\CheckoutServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CheckoutSettingsView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Contract\PdkShippingMethodRepositoryInterface
     */
    private $shippingMethodRepository;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Contract\PdkShippingMethodRepositoryInterface $shippingMethodRepository
     */
    public function __construct(PdkShippingMethodRepositoryInterface $shippingMethodRepository)
    {
        $this->shippingMethodRepository = $shippingMethodRepository;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function createElements(): FormElementCollection
    {
        $deliveryOptionsVisibleProp = [
            '$visibleWhen' => [
                CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
            ],
        ];

        $elements = [
            new InteractiveElement(CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS, Components::INPUT_TOGGLE),

            new InteractiveElement(CheckoutSettings::ENABLE_DELIVERY_OPTIONS, Components::INPUT_TOGGLE),

            new SettingsDivider($this->getSettingKey('delivery_options'), null, $deliveryOptionsVisibleProp),
            new InteractiveElement(
                CheckoutSettings::ALLOWED_SHIPPING_METHODS,
                Components::INPUT_MULTI_SELECT,
                $deliveryOptionsVisibleProp + ['options' => $this->getShippingMethodOptions()]
            ),
            new InteractiveElement(
                CheckoutSettings::PRICE_TYPE,
                Components::INPUT_SELECT,
                $deliveryOptionsVisibleProp + [
                    'options' => $this->createSelectOptions(CheckoutSettings::PRICE_TYPE, [
                        CheckoutSettings::PRICE_TYPE_INCLUDED,
                        CheckoutSettings::PRICE_TYPE_EXCLUDED,
                    ]),
                ]
            ),
            new InteractiveElement(
                CheckoutSettings::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK,
                Components::INPUT_TOGGLE,
                $deliveryOptionsVisibleProp
            ),
            new InteractiveElement(
                CheckoutSettings::DELIVERY_OPTIONS_HEADER,
                Components::INPUT_TEXT,
                $deliveryOptionsVisibleProp
            ),
            new InteractiveElement(
                CheckoutSettings::DELIVERY_OPTIONS_CUSTOM_CSS,
                Components::INPUT_CODE_EDITOR,
                $deliveryOptionsVisibleProp
            ),
        ];

        if (Pdk::has(CheckoutServiceInterface::class)) {
            /** @var \MyParcelNL\Pdk\Plugin\Contract\CheckoutServiceInterface $deliveryOptionsService */
            $checkoutService = Pdk::get(CheckoutServiceInterface::class);

            $elements[] = new InteractiveElement(
                CheckoutSettings::DELIVERY_OPTIONS_POSITION,
                Components::INPUT_SELECT,
                $deliveryOptionsVisibleProp + ['options' => $this->toSelectOptions($checkoutService->getPositions())]
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

    /**
     * @return array
     */
    private function getShippingMethodOptions(): array
    {
        $shippingMethods = $this->shippingMethodRepository->all();

        return $this->toSelectOptions(
            array_reduce(
                $shippingMethods->all(),
                static function (array $cur, PdkShippingMethod $shippingMethod) {
                    $cur[$shippingMethod->id] = "$shippingMethod->name ($shippingMethod->id)";

                    return $cur;
                }, []
            ),
            false,
            true
        );
    }
}
