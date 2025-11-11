<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Repository;

use JsonSchema\Validator;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;

class SchemaRepository extends Repository
{
    /**
     * @param  null|string $carrier
     * @param  null|string $shippingZone
     * @param  null|string $packageType
     * @param  null|string $deliveryType
     *
     * @return string
     */
    public function getKey(
        ?string $carrier = null,
        ?string $shippingZone = null,
        ?string $packageType = null,
        ?string $deliveryType = null
    ): string {
        return implode(
            '/',
            [$carrier ?? '*', $shippingZone ?? '*', $packageType ?? '*', $deliveryType ?? '*']
        );
    }

    /**
     * @param  null|string $carrier
     * @param  null|string $cc
     * @param  null|string $packageType
     * @param  null|string $deliveryType
     *
     * @return array
     */
    public function getOrderValidationSchema(
        ?string $carrier = null,
        ?string $cc = null,
        ?string $packageType = null,
        ?string $deliveryType = null
    ): array {
        return $this->retrieve(
            $this->getKey($carrier, $cc, $packageType, $deliveryType),
            function () use ($deliveryType, $packageType, $cc, $carrier) {
                $schema         = Config::get('schema/order');
                $platformSchema = Config::get(sprintf('validation/%s/order', $this->getPlatformName()));

                /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
                $countryService = Pdk::get(CountryServiceInterface::class);

                $shippingZone = $cc ? $countryService->getShippingZone($cc) : null;

                return $this->mergeSchemas(
                    $schema,
                    $this->narrowSchema([
                        ['attribute' => 'carrier', 'value' => $carrier],
                        ['attribute' => 'shippingZone', 'value' => $cc, 'column' => 'cc'],
                        ['attribute' => 'shippingZone', 'value' => $shippingZone],
                        ['attribute' => 'packageType', 'value' => $packageType],
                        ['attribute' => 'deliveryType', 'value' => $deliveryType],
                    ], $platformSchema)
                );
            }
        );
    }

    /**
     * @param  array  $schema
     * @param  string $path
     *
     * @return array
     */
    public function getValidOptions(array $schema, string $path): array
    {
        return $this->handleOption($schema, $path, []);
    }

    /**
     * @param  array  $schema
     * @param  string $path
     * @param  mixed  $value
     *
     * @return bool
     */
    public function validateOption(array $schema, string $path, $value): bool
    {
        return (bool) $this->handleOption($schema, $path, $value);
    }

    /**
     * @param  array  $schema
     * @param  string $path
     * @param  mixed  $value when array this returns the enum, otherwise will validate against the value
     *
     * @return mixed array when $value is array, bool otherwise
     */
    protected function handleOption(array $schema, string $path, $value)
    {
        $key = $this->generateDataHash($schema);
        $key = is_array($value) ? "option_{$key}_$path" : "option_{$key}_{$path}_$value";

        return $this->retrieve($key, function () use ($schema, $path, $value) {
            $result = Arr::get($schema, $path);

            if (! $result) {
                return is_array($value) ? [] : false;
            }

            if (is_array($value)) {
                return $result['enum'] ?? [];
            }

            $validator = new Validator();
            $validator->validate($value, $result);

            return $validator->isValid();
        });
    }

    /**
     * @param  array $current
     * @param  array $previous
     *
     * @return array
     */
    private function mergeSchemas(array $current, array $previous): array
    {
        foreach ($previous as $key => $value) {
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                $current[$key] = [];
            }

            if (is_array($value) && ! in_array($key, ['enum', 'type'])) {
                $value = $this->mergeSchemas($current[$key], $value);
            }

            $current[$key] = $value;
        }

        return $current;
    }

    /**
     * @param  array $searches
     * @param  array $schema
     *
     * @return array
     */
    private function narrowSchema(
        array $searches,
        array $schema
    ): array {
        $nestedSchema   = [];
        $foundAdditions = $schema;

        foreach ($searches as $values) {
            $value = $values['value'] ?? null;

            if (! $value) {
                continue;
            }

            $additions = $foundAdditions[$values['attribute'] ?? null] ?? [];
            $index     = array_search($value, array_column($additions, $values['column'] ?? 'name'), true);

            if (false === $index) {
                continue;
            }

            $foundAdditions = $additions[$index];

            if (isset($foundAdditions['schema'])) {
                $nestedSchema = $this->mergeSchemas(
                    $nestedSchema,
                    is_string($foundAdditions['schema'])
                        ? Config::get(sprintf('schema/%s/%s', $this->getPlatformName(), $foundAdditions['schema']))
                        : $foundAdditions['schema']
                );
            }
        }

        return $nestedSchema;
    }

    /**
     * Get (legacy) platform name from proposition service as schemas are still named after platforms.
     *
     * @return string
     */
    private function getPlatformName(): string
    {
        $propositionService = Pdk::get(PropositionService::class);
        $proposition = $propositionService->getPropositionConfig();
        $platformConfig = $propositionService->mapToPlatformConfig($proposition);
        return $platformConfig['name'];
    }
}
