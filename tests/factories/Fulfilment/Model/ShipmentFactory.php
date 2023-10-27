<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\ContactDetailsFactory;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationFactory;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Shipment\Model\PhysicalPropertiesFactory;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\RetailLocationFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Shipment
 * @method Shipment make()
 * @method $this withCarrier(int $carrier)
 * @method $this withContractId(string $contractId)
 * @method $this withCustomsDeclaration(CustomsDeclaration|CustomsDeclarationFactory $customsDeclaration)
 * @method $this withOptions(ShipmentOptions|ShipmentOptionsFactory $options)
 * @method $this withPhysicalProperties(PhysicalProperties|PhysicalPropertiesFactory $physicalProperties)
 * @method $this withPickup(RetailLocation|RetailLocationFactory $pickup)
 * @method $this withRecipient(ContactDetails|ContactDetailsFactory $recipient)
 */
final class ShipmentFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Shipment::class;
    }

    /**
     * @param  array|RetailLocation|RetailLocationFactory $dropOffPoint
     *
     * @return $this
     */
    public function withDropOffPoint($dropOffPoint = null): self
    {
        return $this->with(['dropOffPoint' => $dropOffPoint ?? factory(RetailLocation::class)]);
    }
}
