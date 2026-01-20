<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Support\Str;

use function array_map;

/**
 * @deprecated use NewAbstractSettingsView instead
 * @see        \MyParcelNL\Pdk\Frontend\View\NewAbstractSettingsView
 */
abstract class AbstractSettingsView implements Arrayable
{
    public const  SELECT_INCLUDE_OPTION_DEFAULT = 4;
    public const  SELECT_INCLUDE_OPTION_NONE    = 2;
    public const  SELECT_USE_PLAIN_LABEL        = 1;
    private const CACHE_KEY_CHILDREN            = 'children';
    private const CACHE_KEY_CHILDREN_ARRAY      = 'children_array';
    private const CACHE_KEY_ELEMENTS            = 'elements';
    private const CACHE_KEY_ELEMENTS_ARRAY      = 'elements_array';
    private const KEY_PREFIX                    = 'settings';

    protected $cache = [];

    /**
     * @return null|array
     */
    abstract protected function createElements(): ?array;

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

            return $elements
                ? $this->updateElements(new FormElementCollection($this->flattenElements($elements)))
                : null;
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
     * @return null|string
     */
    public function getTitleSuffix(): ?string
    {
        return null;
    }

    /**
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        return [
            'id'          => $this->getSettingsId(),
            'title'       => $this->getTitle(),
            'titleSuffix' => $this->getTitleSuffix(),
            'description' => $this->getDescription(),
            'elements'    => $this->cacheToArray(self::CACHE_KEY_ELEMENTS_ARRAY, [$this, 'getElements']),
            'children'    => $this->cacheToArray(self::CACHE_KEY_CHILDREN_ARRAY, [$this, 'getChildren']),
        ];
    }

    /**
     * @param  array $options
     * @param  int   $displayOptions
     *
     * @return array
     */
    protected function addDefaultOption(array $options, int $displayOptions): array
    {
        $labelKey = $displayOptions & self::SELECT_USE_PLAIN_LABEL ? 'plainLabel' : 'label';

        if ($displayOptions & self::SELECT_INCLUDE_OPTION_DEFAULT) {
            array_unshift($options, [
                'value'   => Settings::OPTION_DEFAULT,
                $labelKey => sprintf('%s_default', self::KEY_PREFIX),
            ]);
        }

        if ($displayOptions & self::SELECT_INCLUDE_OPTION_NONE) {
            array_unshift($options, [
                'value'   => Settings::OPTION_DEFAULT,
                $labelKey => sprintf('%s_none', self::KEY_PREFIX),
            ]);
        }

        return $options;
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection|\MyParcelNL\Pdk\Frontend\View\AbstractSettingsView[]
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
        $propositionService = Pdk::get(PropositionService::class);
        return $this->toSelectOptions(
            array_combine(
                array_values($packageTypes),
                array_map(static function ($packageType) use ($propositionService) {
                    return sprintf('package_type_%s', $propositionService->packageTypeNameForDeliveryOptions($packageType) ?? $packageType);
                }, $packageTypes)
            ),
            self::SELECT_INCLUDE_OPTION_DEFAULT
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
     * @param  PlainElement[]|PlainElement[][] $elements
     * @param  null|callable                   $callback
     *
     * @return array
     */
    protected function flattenElements(array $elements, ?callable $callback = null): array
    {
        return array_reduce($elements, function (array $carry, $element) use ($callback) {
            if (is_array($element)) {
                return array_merge($carry, $this->flattenElements($element, $callback));
            }

            $carry[] = $callback ? $callback($element) : $element;

            return $carry;
        }, []);
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
     * @param  string $name
     *
     * @return bool
     */
    protected function isNotDisabled(string $name): bool
    {
        /** @var array[] $disabledSettings */
        $disabledSettings = Pdk::get('disabledSettings');
        $category         = $disabledSettings[$this->getSettingsId()] ?? [];

        return ! in_array($name, $category, true);
    }

    /**
     * @param  array $array
     * @param  int   $displayOptions
     *
     * @return array
     */
    protected function toSelectOptions(array $array, int $displayOptions = 0): array
    {
        $isAssoc = Arr::isAssoc($array);

        if ($isAssoc) {
            $options = array_map(static function ($value, $key) use ($displayOptions) {
                $labelKey = $displayOptions & self::SELECT_USE_PLAIN_LABEL ? 'plainLabel' : 'label';

                return [
                    'value'   => $key,
                    $labelKey => $value,
                ];
            }, $array, array_keys($array));
        } else {
            $options = array_map(static function (array $selectOption) {
                return array_filter($selectOption);
            }, $array);
        }

        return $this->addDefaultOption($options, $displayOptions);
    }

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection $elements
     *
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function updateElements(FormElementCollection $elements): FormElementCollection
    {
        return $elements
            ->filter(function ($element): bool {
                if (! isset($element->name) || ! $element instanceof InteractiveElement) {
                    return true;
                }

                return $this->isNotDisabled($element->name);
            })
            ->map(function ($element): PlainElement {
                if ($element instanceof InteractiveElement) {
                    $label       = $this->createLabel($this->getLabelPrefix(), $element->name);
                    $description = "{$label}_description";

                    $element->props['label'] = $label;

                    if (Language::hasTranslation($description)) {
                        $element->props['description'] = $description;
                    }
                }

                return $element;
            })
            ->values();
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
     * @param  callable                    $callback
     * @param  PlainElement[]|PlainElement ...$fields
     *
     * @return array
     */
    protected function withOperation(callable $callback, ...$fields): array
    {
        return $this->flattenElements($fields, static function (PlainElement $field) use ($callback) {
            $field->builder($callback);

            return $field;
        });
    }

    /**
     * @param  array                       $props
     * @param  PlainElement[]|PlainElement ...$fields
     *
     * @return array
     */
    protected function withProps(array $props, ...$fields): array
    {
        return $this->flattenElements($fields, static function (PlainElement $field) use ($props) {
            $field->props = array_replace_recursive($field->props, $props);

            return $field;
        });
    }

    /**
     * @param  string   $key
     * @param  callable $closure
     *
     * @return array|null
     */
    private function cacheToArray(string $key, callable $closure): ?array
    {
        return $this->cacheValue($key, function () use ($closure): ?array {
            /** @var null|Collection $data */
            $data = $closure();

            return $data ? $data->toArrayWithoutNull() : null;
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
