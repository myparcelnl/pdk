<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Concern;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;

/**
 * @see ElementBuilderWithOptionsInterface
 */
trait HasOptions
{
    /**
     * @var array
     */
    private $options;

    public function withOptions(array $options, int $flags = 0): ElementBuilderWithOptionsInterface
    {
        $this->addHook(
            ElementBuilderInterface::HOOK_PROPS,
            function () use ($options, $flags) {
                $this->withProp('options', $this->toSelectOptions($options, $flags));

                if ($flags & ElementBuilderWithOptionsInterface::SORT_ASC) {
                    $this->withSort(ElementBuilderWithOptionsInterface::SORT_ASC_VALUE);
                }

                if ($flags & ElementBuilderWithOptionsInterface::SORT_DESC) {
                    $this->withSort(ElementBuilderWithOptionsInterface::SORT_DESC_VALUE);
                }
            }
        );

        return $this;
    }

    public function withSort(string $sort): ElementBuilderWithOptionsInterface
    {
        return $this->withProp('sort', $sort);
    }

    protected function addDefaultOption(array $options, int $flags = 0): array
    {
        if ($flags & ElementBuilderWithOptionsInterface::ADD_DEFAULT) {
            array_unshift($options, [
                'value' => Settings::OPTION_DEFAULT,
                'label' => sprintf('%s_default', Arr::first($this->prefixes)),
            ]);
        }

        if ($flags & ElementBuilderWithOptionsInterface::ADD_NONE) {
            array_unshift($options, [
                'value' => Settings::OPTION_NONE,
                'label' => sprintf('%s_none', Arr::first($this->prefixes)),
            ]);
        }

        return $options;
    }

    protected function toSelectOptions(array $array, int $flags = 0): array
    {
        $associativeArray = Arr::isAssoc($array) ? $array : array_combine($array, $array);

        $options = array_map(function (string $key, $value) use ($flags) {
            $usePlainLabel = $flags & ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL;
            $labelKey      = $usePlainLabel ? 'plainLabel' : 'label';

            return [
                $labelKey => $usePlainLabel ? $key : $this->createOptionLabel($key),
                'value'   => $value,
            ];
        }, array_values($associativeArray), array_keys($associativeArray));

        return $this->addDefaultOption($options, $flags);
    }

    private function createOptionLabel(mixed $value): string
    {
        return $this->createLabel($this->getName(), 'option', (string) $value);
    }
}
