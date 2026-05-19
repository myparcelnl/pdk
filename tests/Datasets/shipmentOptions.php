<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Datasets;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PriorityDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;

function getFrontendShipmentOptions(): array
{
    return [
        'only recipient' => new OnlyRecipientDefinition(),
        'priority delivery' => new PriorityDeliveryDefinition(),
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

function getShipmentOptionsWithProductSettings(): array
{
    return array_filter(
        getAllShipmentOptions(),
        static function (OrderOptionDefinitionInterface $definition): bool {
            return null !== $definition->getProductSettingsKey();
        }
    );
}

dataset('frontend shipment options', function () { return getFrontendShipmentOptions(); });
dataset('all shipment options', function () { return getAllShipmentOptions(); });
dataset('shipment options with product settings', function () { return getShipmentOptionsWithProductSettings(); });
