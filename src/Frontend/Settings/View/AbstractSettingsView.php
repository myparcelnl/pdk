<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

abstract class AbstractSettingsView implements Arrayable
{
    public const  OPTIONS_VALUE_NONE       = -1;
    private const CACHE_KEY_CHILDREN       = 'children';
    private const CACHE_KEY_CHILDREN_ARRAY = 'children_array';
    private const CACHE_KEY_ELEMENTS       = 'elements';
    private const CACHE_KEY_ELEMENTS_ARRAY = 'elements_array';
    private const KEY_PREFIX               = 'settings';

    protected $cache = [];

    /**
     * @return null|\MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    abstract protected function createElements(): ?FormElementCollection;

    /**
     * @return string
     */
    abstract protected function getSettingsId(): string;

    /**
     * @return null|\MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getChildren(): ?Collection
    {
        return $this->cacheValue(self::CACHE_KEY_CHILDREN, [$this, 'createChildren']);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->createLabel('view', $this->getLabelPrefix(), 'description');
    }

    /**
     * @return null|\MyParcelNL\Pdk\Frontend\Collection\FormElementCollection|PlainElement[]
     */
    public function getElements(): ?FormElementCollection
    {
        return $this->cacheValue(self::CACHE_KEY_ELEMENTS, function (): ?FormElementCollection {
            $elements = $this->createElements();

            return $elements ? $this->updateElements($elements) : null;
        });
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->createLabel('view', $this->getLabelPrefix(), 'title');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->getSettingsId(),
            'title'       => $this->getTitle(),
            'description' => $this->getDescription(),
            'elements'    => $this->cacheToArray(self::CACHE_KEY_ELEMENTS_ARRAY, [$this, 'getElements']),
            'children'    => $this->cacheToArray(self::CACHE_KEY_CHILDREN_ARRAY, [$this, 'getChildren']),
        ];
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection|\MyParcelNL\Pdk\Frontend\Settings\View\AbstractSettingsView[]
     */
    protected function createChildren(): ?Collection
    {
        return null;
    }

    /**
     * @param  string ...$keys
     *
     * @return string
     */
    protected function createLabel(string ...$keys): string
    {
        return Str::snake(implode('_', array_merge([self::KEY_PREFIX], $keys)));
    }

    /**
     * @param  string $setting
     * @param  string $option
     *
     * @return string
     */
    protected function createOptionLabel(string $setting, string $option): string
    {
        return Str::snake(sprintf('%s_option_%s', $this->getSettingKey($setting), $option));
    }

    /**
     * @param  array $packageTypes
     *
     * @return array
     */
    protected function createPackageTypeOptions(array $packageTypes = DeliveryOptions::PACKAGE_TYPES_NAMES): array
    {
        return $this->toSelectOptions(
            array_map(static function (string $packageTypeName) {
                return "package_type_$packageTypeName";
            }, $packageTypes)
        );
    }

    /**
     * @param  string $settingsKey
     * @param  array  $options
     *
     * @return array
     */
    protected function createSelectOptions(string $settingsKey, array $options): array
    {
        return $this->toSelectOptions(
            array_combine(
                array_values($options),
                array_map(function ($option) use ($settingsKey) {
                    return $this->createOptionLabel($settingsKey, (string) $option);
                }, $options)
            )
        );
    }

    /**
     * @return string
     */
    protected function getLabelPrefix(): string
    {
        return $this->getSettingsId();
    }

    /**
     * @param  string $name
     *
     * @return string
     */
    protected function getSettingKey(string $name): string
    {
        return Str::snake(sprintf('%s_%s_%s', self::KEY_PREFIX, $this->getSettingsId(), $name));
    }

    /**
     * @param  array $array
     * @param  bool  $includeNone
     * @param  bool  $plainLabels
     *
     * @return array
     */
    protected function toSelectOptions(array $array, bool $includeNone = false, bool $plainLabels = false): array
    {
        $associativeArray = (Arr::isAssoc($array) ? $array : array_combine($array, $array)) ?? [];

        $options = array_map(static function ($value, $key) use ($plainLabels) {
            $labelKey = $plainLabels ? 'plainLabel' : 'label';

            return [
                'value'   => $key,
                $labelKey => $value,
            ];
        }, $associativeArray, array_keys($associativeArray));

        if ($includeNone) {
            array_unshift($options, [
                'value' => self::OPTIONS_VALUE_NONE,
                'label' => sprintf('%s_none', self::KEY_PREFIX),
            ]);
        }

        return $options;
    }

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection $elements
     *
     * @return mixed|\MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function updateElements(FormElementCollection $elements)
    {
        return $elements->map(function (PlainElement $element): PlainElement {
            if ($element instanceof InteractiveElement) {
                $label       = $this->createLabel($this->getLabelPrefix(), $element->name);
                $description = "{$label}_description";

                $element->props['label'] = $label;

                if (Language::hasTranslation($description)) {
                    $element->props['description'] = $description;
                }
            }

            return $element;
        });
    }

    /**
     * @param  array $item
     */
    protected function validate(array $item): void
    {
        if (isset($item['type'])) {
            throw new InvalidArgumentException('Property "type" can not be manually set. Use "class" instead.');
        }

        if (! isset($item['name'], $item['class'])) {
            throw new InvalidArgumentException(sprintf('Fields "name" and "class" are required in %s', $item['class']));
        }
    }

    /**
     * @param  string   $key
     * @param  callable $closure
     *
     * @return void
     */
    private function cacheToArray(string $key, callable $closure): ?array
    {
        return $this->cacheValue($key, function () use ($closure): ?array {
            $data = $closure();

            return $data ? $data->toArray() : null;
        });
    }

    /**
     * @param  string   $key
     * @param  callable $closure
     *
     * @return mixed
     */
    private function cacheValue(string $key, callable $closure)
    {
        $resolvedKey = sprintf('%s.%s', static::class, $key);

        if (! array_key_exists($resolvedKey, $this->cache)) {
            $this->cache[$resolvedKey] = $closure();
        }

        return $this->cache[$resolvedKey];
    }
}
