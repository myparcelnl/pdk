<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Support\Str;

it('derives capability names for generated option properties without a local mapping', function () {
    $responseProperties = RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap();

    foreach (array_keys(CapabilitiesOptionsV2::attributeMap()) as $property) {
        $definition = new class(Str::camel($property)) extends AbstractOrderOptionDefinition {
            private string $key;

            public function __construct(string $key)
            {
                $this->key = $key;
            }

            public function getShipmentOptionsKey(): ?string
            {
                return $this->key;
            }
        };

        expect($definition->getCapabilitiesOptionsKey())->toBe($responseProperties[$property] ?? null);
    }
});

it('normalizes renamed camelCase inputs before asking the SDK', function () {
    expect((new AgeCheckDefinition())->getCapabilitiesOptionsKey())->toBe('requiresAgeVerification')
        ->and((new OnlyRecipientDefinition())->getCapabilitiesOptionsKey())->toBe('recipientOnlyDelivery')
        ->and((new DirectReturnDefinition())->getCapabilitiesOptionsKey())->toBe('returnOnFirstFailedDelivery');
});

it('returns null when an option has no generated capability mapping', function (?string $key) {
    $definition = new class($key) extends AbstractOrderOptionDefinition {
        private ?string $key;

        public function __construct(?string $key)
        {
            $this->key = $key;
        }

        public function getShipmentOptionsKey(): ?string
        {
            return $this->key;
        }
    };

    expect($definition->getCapabilitiesOptionsKey())->toBeNull();
})->with([null, '', 'unknownOption']);
