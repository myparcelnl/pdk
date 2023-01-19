<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;

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
        'lines'                 => null,
        'shipmentPrice'         => 0,
        'shipmentPriceAfterVat' => 0,
        'shipmentVat'           => 0,
        'orderPrice'            => 0,
        'orderPriceAfterVat'    => 0,
        'orderVat'              => 0,
        'totalPrice'            => 0,
        'totalPriceAfterVat'    => 0,
        'totalVat'              => 0,
        'physicalProperties'    => null,
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
        'physicalProperties'    => PhysicalProperties::class,
        'shippingMethod'        => PdkShippingMethod::class,
    ];

    public function __construct(?array $data)
    {
        parent::__construct($data);
        try {
            $this->updatePropertiesFromLines();
        } catch (InvalidCastException $e) {
            // todo log?
        }
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function updatePropertiesFromLines(): void
    {
        $weight                 = 0.0;
        $mailboxPercentage      = 0.0;
        $allowedPackageTypes    = ['letter', 'digital_stamp', 'mailbox', 'package']; // todo use constants
        $minimumDropOffDelay    = 0;
        $disableDeliveryOptions = false;
        foreach ($this->lines as $line) {
            if (! $line instanceof PdkOrderLine) {
                $line = new PdkOrderLine($line);
            }
            $quantity            = $line->quantity;
            $weight              += $quantity * $line->product->weight;
            $fitInMailbox        = (int) $line->product->settings->fitInMailbox;
            $minimumDropOffDelay = max($minimumDropOffDelay, $line->product->settings->dropOffDelay);
            if ($mailboxPercentage <= 100.0 && 0 !== $fitInMailbox) {
                $mailboxPercentage += $quantity * (100.0 / $fitInMailbox);
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
        if ($mailboxPercentage > 100.0 && in_array('mailbox', $allowedPackageTypes)) {
            unset($allowedPackageTypes[array_search('mailbox', $allowedPackageTypes)]);
        }
        //        $this->lines->each(function (PdkOrderLine $line) { // todo use the correct pattern, not foreach
        //        });

        $attributes           = $this->shippingMethod->toArray(); // todo when the model is fixed, just update the props directly
        $this->shippingMethod = [
                'disableDeliveryOptions' => $disableDeliveryOptions,
                'minimumDropOffDelay'    => $minimumDropOffDelay,
                'allowPackageTypes'      => array_values($allowedPackageTypes), // todo use the packagetypecollection?
                'preferPackageType'      => reset($allowedPackageTypes),
            ] + $attributes;
        $this->setAttribute(
            'physicalProperties',
            new PhysicalProperties([
                'weight' => $weight,
            ])
        );
    }
}
