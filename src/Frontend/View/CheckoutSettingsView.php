<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CheckoutSettingsView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface
     */
    private $shippingMethodRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface $shippingMethodRepository
     */
    public function __construct(PdkShippingMethodRepositoryInterface $shippingMethodRepository)
    {
        $this->shippingMethodRepository = $shippingMethodRepository;
    }

    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        return [
            new InteractiveElement(CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS, Components::INPUT_TOGGLE),
            new InteractiveElement(CheckoutSettings::ENABLE_ADDRESS_WIDGET, Components::INPUT_TOGGLE),
            new SettingsDivider($this->getSettingKey('delivery_options')),
            new InteractiveElement(CheckoutSettings::ENABLE_DELIVERY_OPTIONS, Components::INPUT_TOGGLE),

            $this->withOperation(
                function (FormOperationBuilder $builder) {
                    $builder->visibleWhen(CheckoutSettings::ENABLE_DELIVERY_OPTIONS);
                },
                new InteractiveElement(
                    CheckoutSettings::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK,
                    Components::INPUT_TOGGLE
                ),
                new InteractiveElement(
                    CheckoutSettings::DELIVERY_OPTIONS_POSITION,
                    Components::INPUT_SELECT,
                    [
                        'options' => $this->createSelectOptions(
                            CheckoutSettings::DELIVERY_OPTIONS_POSITION,
                            Pdk::get('deliveryOptionsPositions')
                        ),
                    ]
                ),
                new InteractiveElement(
                    CheckoutSettings::ALLOWED_SHIPPING_METHODS,
                    Components::INPUT_SHIPPING_METHODS,
                    ['options' => $this->getShippingMethodOptions()]
                ),
                new InteractiveElement(
                    CheckoutSettings::PRICE_TYPE,
                    Components::INPUT_SELECT,
                    [
                        'options' => $this->createSelectOptions(CheckoutSettings::PRICE_TYPE, [
                            CheckoutSettings::PRICE_TYPE_INCLUDED,
                            CheckoutSettings::PRICE_TYPE_EXCLUDED,
                        ]),
                    ]
                ),
                new InteractiveElement(CheckoutSettings::DELIVERY_OPTIONS_HEADER, Components::INPUT_TEXT),
                new InteractiveElement(CheckoutSettings::DELIVERY_OPTIONS_CUSTOM_CSS, Components::INPUT_CODE_EDITOR),
                new InteractiveElement(
                    CheckoutSettings::PICKUP_LOCATIONS_STYLE,
                    Components::INPUT_SELECT,
                    [
                        'options' => $this->createSelectOptions(
                            CheckoutSettings::PICKUP_LOCATIONS_STYLE,
                            [
                                CheckoutSettings::PICKUP_LOCATIONS_STYLE_DEFAULT,
                                CheckoutSettings::PICKUP_LOCATIONS_STYLE_MAP,
                                CheckoutSettings::PICKUP_LOCATIONS_STYLE_LIST,
                            ]
                        ),
                        'helpText' => $this->getSettingKey('pickup_locations_style_help'),
                    ]
                ),
                $this->withOperation(
                    function (FormOperationBuilder $builder) {
                        $builder->visibleWhen(CheckoutSettings::PICKUP_LOCATIONS_STYLE, CheckoutSettings::PICKUP_LOCATIONS_STYLE_DEFAULT);
                    },
                    new InteractiveElement(
                        CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW,
                        Components::INPUT_SELECT,
                        [
                            'options' => $this->createSelectOptions(
                                CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW,
                                [
                                    CheckoutSettings::PICKUP_LOCATIONS_VIEW_LIST,
                                    CheckoutSettings::PICKUP_LOCATIONS_VIEW_MAP,
                                ]
                            ),
                        ]
                    )
                ),
                new InteractiveElement(CheckoutSettings::SHOW_TAX_FIELDS, Components::INPUT_TOGGLE)
            ),
        ];
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
        $allShippingMethods = $this->shippingMethodRepository->all();

        $array = $allShippingMethods
            ->filter(static function (PdkShippingMethod $shippingMethod) {
                return $shippingMethod->isEnabled;
            })
            ->reduce(static function (array $cur, PdkShippingMethod $shippingMethod) {
                $cur[] = [
                    'value'       => $shippingMethod->id,
                    'plainLabel'  => $shippingMethod->name,
                    'description' => $shippingMethod->description,
                ];

                return $cur;
            }, []);

        return $this->toSelectOptions($array, AbstractSettingsView::SELECT_USE_PLAIN_LABEL);
    }
}
