<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\App\Request\Collection\EndpointRequestCollection;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @property null|DeliveryOptionsConfig $config
 * @property array{string,string}       $strings
 * @property array                      $settings
 */
class CheckoutContext extends Model
{
    public $attributes = [
        'config'    => null,
        'platformConfig' => null,
        'strings'   => [],
        'settings'  => [],
        'endpoints' => EndpointRequestCollection::class,
    ];

    protected $casts      = [
        'config'    => DeliveryOptionsConfig::class,
        'platformConfig' => 'array',
        'strings'   => 'array',
        'settings'  => 'array',
        'endpoints' => EndpointRequestCollection::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->attributes['strings']  = $this->getStrings();
        $this->attributes['settings'] = $this->getSettings();
        $this->attributes['platformConfig'] = Pdk::get(DeliveryOptionsService::class)->createPropositionConfig();
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return self
     */
    public static function fromCart(PdkCart $cart): self
    {
        return new self([

            'config'   => DeliveryOptionsConfig::fromCart($cart),
        ,
            'settings' => [
                'hasDeliveryOptions' => $cart->shippingMethod->hasDeliveryOptions,
            ],
        ]);
    }

    /**
     * @return array
     */
    private function getActions(): array
    {
        /** @var \MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface $frontendEndpointService */
        $frontendEndpointService = Pdk::get(FrontendEndpointServiceInterface::class);

        return [
            'baseUrl'   => $frontendEndpointService->getBaseUrl(),
            'endpoints' => $frontendEndpointService->toArray(),
        ];
    }

    /**
     * @return array
     */
    private function getSettings(): array
    {
        $settings = $this->getAttributeValue('settings');

        return array_merge([
            /** General */
            'actions'                            => $this->getActions(),

            /** Delivery options */
            'allowedShippingMethods'             => Settings::get(
                CheckoutSettings::ALLOWED_SHIPPING_METHODS,
                CheckoutSettings::ID
            ),
            'hasDeliveryOptions'                 => Settings::get(
                CheckoutSettings::ENABLE_DELIVERY_OPTIONS,
                CheckoutSettings::ID
            ),
            'hiddenInputName'                    => Pdk::get('checkoutHiddenInputName'),
            'checkoutAddressHiddenInputName'     => Pdk::get('checkoutAddressHiddenInputName'),

            /** Separate address fields */
            'countriesWithSeparateAddressFields' => Pdk::get('countriesWithSeparateAddressFields'),

            /** Tax fields */
            'carriersWithTaxFields'              => AccountSettings::hasTaxFields()
                ? Pdk::get('carriersWithTaxFields')
                : [],

            /* Address widget */
            'hasAddressWidget'                    => Settings::get(
                CheckoutSettings::ENABLE_ADDRESS_WIDGET,
                CheckoutSettings::ID
            ),
        ], $settings);
    }

    /**
     * @return array
     */
    private function getStrings(): array
    {
        $prefix  = Pdk::get('translationPrefixDeliveryOptions');
        $strings = (new Collection(Language::getTranslations()))
            ->filter(static function ($value, $key) use ($prefix) {
                return Str::startsWith($key, $prefix);
            })
            ->mapWithKeys(static function ($value, $key) use ($prefix) {
                $replacedKey = Str::after($key, $prefix);
                $finalKey    = Str::camel($replacedKey);

                return [$finalKey => $value];
            })
            ->toArray();

        return array_merge($strings, [
            'headerDeliveryOptions' => Settings::get(CheckoutSettings::DELIVERY_OPTIONS_HEADER, CheckoutSettings::ID),
        ]);
    }
}
