<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

abstract class AbstractSettingsView implements Arrayable
{
    public const  OPTIONS_VALUE_NONE = -1;
    private const KEY_PREFIX         = 'settings';

    protected $cache = [];

    public function getChildren(): ?array
    {
        if (! array_key_exists('children', $this->cache)) {
            $children = $this->createChildren();

            if ($children) {
                $this->cache['children'] = $children->toArray();
            } else {
                $this->cache['children'] = null;
            }
        }

        return $this->cache['children'];
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->createLabel('view', $this->getLabelPrefix(), 'description');
    }

    /**
     * @return null|array
     */
    public function getElements(): ?array
    {
        if (! array_key_exists('elements', $this->cache)) {
            $elements = $this->createElements();

            if ($elements) {
                $this->cache['elements'] = $elements->map(function (PlainElement $element) {
                    if ($element instanceof InteractiveElement) {
                        $label       = $this->createLabel($this->getLabelPrefix(), $element->name);
                        $description = "{$label}_description";

                        $element->props['label'] = $label;

                        if (LanguageService::hasTranslation($description)) {
                            $element->props['description'] = $description;
                        }
                    }

                    return $element->toArray();
                })
                    ->toArray();
            } else {
                $this->cache['elements'] = null;
            }
        }

        return $this->cache['elements'];
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
            'elements'    => $this->getElements(),
            'children'    => $this->getChildren(),
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
     * @return null|\MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    abstract protected function createElements(): ?FormElementCollection;

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
                    return $this->createOptionLabel($settingsKey, $option);
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
     * @return string
     */
    abstract protected function getSettingsId(): string;

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
}
