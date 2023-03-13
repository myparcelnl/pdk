<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Collection\EndpointRequestCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

/**
 * @property null|DeliveryOptionsConfig $config
 * @property array{string,string}       $strings
 * @property array                      $settings
 */
class CheckoutContext extends Model
{
    public    $attributes = [
        'config'    => null,
        'strings'   => [],
        'settings'  => [],
        'endpoints' => EndpointRequestCollection::class,
    ];

    protected $casts      = [
        'config'    => DeliveryOptionsConfig::class,
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
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return self
     */
    public static function fromCart(PdkCart $cart): self
    {
        return new self(['config' => DeliveryOptionsConfig::fromCart($cart)]);
    }

    /**
     * @return void
     */
    private function getSettings(): array
    {
        /** @var \MyParcelNL\Pdk\Plugin\Api\Contract\FrontendEndpointServiceInterface $frontendEndpointService */
        $frontendEndpointService = Pdk::get(FrontendEndpointServiceInterface::class);

        // todo not everything here belongs in pdk
        return [
            'allowedShippingMethods'      => [],
            'alwaysShow'                  => true,
            'disallowedShippingMethods'   => ['free_shipping', 'local_pickup'],
            'hiddenInputName'             => sprintf('%s_delivery_options', Pdk::getAppInfo()->name),
            'isUsingSplitAddressFields'   => (int) Settings::get(
                CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS,
                CheckoutSettings::ID
            ),
            'splitAddressFieldsCountries' => Pdk::get('splitAddressFieldsCountries'),
            'actions'                     => [
                'baseUrl'   => $frontendEndpointService->getBaseUrl(),
                'endpoints' => $frontendEndpointService->toArray(),
            ],
        ];
    }

    /**
     * @return array
     */
    private function getStrings(): array
    {
        return LanguageService::translateArray([
                'addressNotFound'           => 'delivery_options_address_not_found',
                'cc'                        => 'delivery_options_cc',
                'city'                      => 'delivery_options_city',
                'closed'                    => 'delivery_options_closed',
                'deliveryEveningTitle'      => 'delivery_options_delivery_type_evening_title',
                'deliveryMorningTitle'      => 'delivery_options_delivery_type_morning_title',
                'deliverySameDayTitle'      => 'delivery_options_delivery_type_same_day_title',
                'deliveryStandardTitle'     => 'delivery_options_delivery_type_standard_title',
                'deliveryTitle'             => 'delivery_options_delivery_title',
                'discount'                  => 'delivery_options_discount',
                'free'                      => 'delivery_options_free',
                'from'                      => 'delivery_options_from',
                'loadMore'                  => 'delivery_options_load_more',
                'mondayDeliveryTitle'       => 'delivery_options_monday_delivery_title',
                'number'                    => 'delivery_options_number',
                'onlyRecipientTitle'        => 'delivery_options_only_recipient_title',
                'openingHours'              => 'delivery_options_opening_hours',
                'options'                   => 'delivery_options_options',
                'packageTypeDigitalStamp'   => 'delivery_options_package_type_digital_stamp',
                'packageTypeMailbox'        => 'delivery_options_package_type_mailbox',
                'pickUpFrom'                => 'delivery_options_pick_up_from',
                'pickupLocationsListButton' => 'delivery_options_pickup_locations_list_button',
                'pickupLocationsMapButton'  => 'delivery_options_pickup_locations_map_button',
                'pickupTitle'               => 'delivery_options_pickup_title',
                'postalCode'                => 'delivery_options_postal_code',
                'saturdayDeliveryTitle'     => 'delivery_options_saturday_delivery_title',
                'signatureTitle'            => 'delivery_options_signature_title',
            ]) + [
                'headerDeliveryOptions' => Settings::get(
                    CheckoutSettings::ID,
                    CheckoutSettings::DELIVERY_OPTIONS_HEADER
                ),
            ];
    }
}
