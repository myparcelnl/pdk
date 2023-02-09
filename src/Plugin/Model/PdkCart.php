<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use Exception;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property string                                                   $externalIdentifier
 * @property \MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection $lines
 * @property int                                                      $shipmentPrice
 * @property int                                                      $shipmentPriceAfterVat
 * @property int                                                      $shipmentVat
 * @property int                                                      $orderPrice
 * @property int                                                      $orderPriceAfterVat
 * @property int                                                      $orderVat
 * @property int                                                      $totalPrice
 * @property int                                                      $totalPriceAfterVat
 * @property int                                                      $totalVat
 * @property \MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod           $shippingMethod
 */
class PdkCart extends Model
{
    protected $attributes = [
        'externalIdentifier'    => null,
        'lines'                 => PdkOrderLineCollection::class,
        'shipmentPrice'         => 0,
        'shipmentPriceAfterVat' => 0,
        'shipmentVat'           => 0,
        'orderPrice'            => 0,
        'orderPriceAfterVat'    => 0,
        'orderVat'              => 0,
        'totalPrice'            => 0,
        'totalPriceAfterVat'    => 0,
        'totalVat'              => 0,
        'shippingMethod'        => null,
    ];

    protected $casts      = [
        'externalIdentifier'    => 'string',
        'lines'                 => PdkOrderLineCollection::class,
        'shipmentPrice'         => 'int',
        'shipmentPriceAfterVat' => 'int',
        'shipmentVat'           => 'int',
        'orderPrice'            => 'int',
        'orderPriceAfterVat'    => 'int',
        'orderVat'              => 'int',
        'totalPrice'            => 'int',
        'totalPriceAfterVat'    => 'int',
        'totalVat'              => 'int',
        'shippingMethod'        => PdkShippingMethod::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->updateShippingMethod();
    }

    /**
     * @param  mixed $value
     *
     * @return self
     */
    public function setShippingMethodAttribute($value): self
    {
        $this->attributes['shippingMethod'] = $value;
        $this->updateShippingMethod();
        return $this;
    }

    private function updateShippingMethod(): void
    {
        try {
            $mailboxPercentage      = 0;
            $allowedPackageTypes    = [
                DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ];
            $minimumDropOffDelay    = 0;
            $disableDeliveryOptions = false;

            foreach ($this->lines->all() as $line) {
                /** @var \MyParcelNL\Pdk\Plugin\Model\PdkOrderLine $line */

                $fitInMailbox = $line->product->settings->fitInMailbox;

                $minimumDropOffDelay = max($minimumDropOffDelay, $line->product->settings->dropOffDelay);

                if ($mailboxPercentage <= 100 && 0 !== $fitInMailbox) {
                    $mailboxPercentage += $line->quantity * (100 / $fitInMailbox);
                }

                foreach ($allowedPackageTypes as $index => $allowedPackageType) {
                    if ($allowedPackageType === $line->product->settings->packageType) {
                        break;
                    }
                    unset($allowedPackageTypes[$index]);
                }

                if (1 === (int) $line->product->settings->disableDeliveryOptions) {
                    $disableDeliveryOptions = true;
                }
            }

            if ($mailboxPercentage > 100 && in_array(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, $allowedPackageTypes, true)) {
                unset($allowedPackageTypes[array_search(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, $allowedPackageTypes, true)]);
            }

            $this->shippingMethod->fill(
                [
                    'disableDeliveryOptions' => $disableDeliveryOptions,
                    'minimumDropOffDelay'    => $minimumDropOffDelay,
                    'allowPackageTypes'      => array_values($allowedPackageTypes),
                    // todo use the packagetypecollection?
                    'preferPackageType'      => reset($allowedPackageTypes),
                ]
            );
        } catch (Exception $e) {
            DefaultLogger::error($e->getMessage(), ['exception' => $e]);
        }
    }
}
