<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Concern;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

/**
 * @implements ElementBuilderWithOptionsInterface
 */
trait HasOptions
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param  array $options
     * @param  int   $flags
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface
     */
    public function withOptions(array $options, int $flags = 0): ElementBuilderWithOptionsInterface
    {
        $this->addHook(
            ElementBuilderInterface::HOOK_PROPS,
            function () use ($options, $flags) {
                $this->withProp('options', $this->toSelectOptions($options, $flags));
            }
        );

        return $this;
    }

    /**
     * @param  array $options
     * @param  int   $flags
     *
     * @return array
     */
    protected function addDefaultOption(array $options, int $flags = 0): array
    {
        if ($flags & ElementBuilderWithOptionsInterface::ADD_DEFAULT) {
            array_unshift($options, [
                'value' => ElementBuilderWithOptionsInterface::VALUE_DEFAULT,
                'label' => 'option_default',
            ]);
        }

        if ($flags & ElementBuilderWithOptionsInterface::ADD_NONE) {
            array_unshift($options, [
                'value' => ElementBuilderWithOptionsInterface::VALUE_NONE,
                'label' => 'option_none',
            ]);
        }

        return $options;
    }

    /**
     * @param  array $array
     * @param  int   $flags
     *
     * @return array
     */
    protected function toSelectOptions(array $array, int $flags = 0): array
    {
        if (! Arr::isAssoc($array) && is_array(Arr::first($array))) {
            return $this->addDefaultOption($array, $flags);
        }

        $associativeArray = (Arr::isAssoc($array) ? $array : array_combine($array, $array)) ?? [];

        $options = array_map(function ($value, string $key) use ($flags) {
            $usePlainLabel = $flags & ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL;
            $labelKey      = $usePlainLabel ? 'plainLabel' : 'label';

            return [
                $labelKey => $usePlainLabel ? $key : $this->createOptionLabel($key),
                'value'   => $value,
            ];
        }, $associativeArray, array_keys($associativeArray));

        return $this->addDefaultOption($options, $flags);
    }

    /**
     * @param  mixed $value
     *
     * @return string
     */
    private function createOptionLabel($value): string
    {
        return $this->createLabel($this->getName(), 'option', (string) $value);
    }
}
