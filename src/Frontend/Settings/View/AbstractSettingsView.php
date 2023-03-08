<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Support\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

abstract class AbstractSettingsView implements Arrayable
{
    public const OPTIONS_VALUE_NONE      = -1;

    protected $cache = [];

    /**
     * @return null|\MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    abstract protected function getElements(): ?FormElementCollection;

    /**
     * @return string
     */
    abstract protected function getSettingsId(): string;

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->createLabel('view', $this->getLabelPrefix(), 'description');
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
            'elements'    => $this->getCachedElements(),
            'children'    => $this->getCachedChildren(),
        ];
    }

    /**
     * @param  string ...$keys
     *
     * @return string
     */
    protected function createLabel(string ...$keys): string
    {
        return Str::snake(implode('_', array_merge(['settings'], $keys)));
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection|\MyParcelNL\Pdk\Frontend\Settings\View\AbstractSettingsView[]
     */
    protected function getChildren(): ?Collection
    {
        return null;
    }

    /**
     * @return string
     */
    protected function getLabelPrefix(): string
    {
        return $this->getSettingsId();
    }

    /**
     * @param  array $array
     * @param  bool  $includeNone
     *
     * @return array
     */
    protected function toSelectOptions(array $array, bool $includeNone = false): array
    {
        $associativeArray = (Arr::isAssoc($array) ? $array : array_combine($array, $array)) ?? [];

        $options = array_map(static function ($value, $key) {
            return [
                'value' => $key,
                'label' => $value,
            ];
        }, $associativeArray, array_keys($associativeArray));

        if ($includeNone) {
            array_unshift($options, [
                'value' => self::OPTIONS_VALUE_NONE,
                'label' => LanguageService::translate('settings_none'),
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

    /**
     * @return array
     */
    private function getCachedChildren(): ?array
    {
        if (! array_key_exists('children', $this->cache)) {
            $children = $this->getChildren();

            if ($children) {
                $this->cache['children'] = $children->toArray();
            } else {
                $this->cache['children'] = null;
            }
        }

        return $this->cache['children'];
    }

    /**
     * @return null|array
     */
    private function getCachedElements(): ?array
    {
        if (! array_key_exists('elements', $this->cache)) {
            $elements = $this->getElements();

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
}
