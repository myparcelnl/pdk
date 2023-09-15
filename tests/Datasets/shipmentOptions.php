<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Datasets;

use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CountryOfOriginDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CustomsCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DisableDeliveryOptionsDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DropOffDelayDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInDigitalStampDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInMailboxDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PackageTypeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;

function getFrontendShipmentOptions(): array
{
    return [
        'only recipient' => new OnlyRecipientDefinition(),
        'signature'      => new SignatureDefinition(),
    ];
}

function getAllShipmentOptions(): array
{
    return array_merge(getFrontendShipmentOptions(), [
        'age check'     => new AgeCheckDefinition(),
        'large format'  => new LargeFormatDefinition(),
        'direct return' => new DirectReturnDefinition(),
    ]);
}

function getProductOptions(): array
{
    return array_merge(getAllShipmentOptions(), [
        'country of origin'        => new CountryOfOriginDefinition(),
        'customs code'             => new CustomsCodeDefinition(),
        'disable delivery options' => new DisableDeliveryOptionsDefinition(),
        'drop off delay'           => new DropOffDelayDefinition(),
        'fit in digital stamp'     => new FitInDigitalStampDefinition(),
        'fit in mailbox'           => new FitInMailboxDefinition(),
        'package type'             => new PackageTypeDefinition(),
    ]);
}

dataset('frontend shipment options', fn() => getFrontendShipmentOptions());
dataset('all shipment options', fn() => getAllShipmentOptions());
dataset('product options', fn() => getProductOptions());
