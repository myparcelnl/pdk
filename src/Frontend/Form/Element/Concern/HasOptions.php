<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Concern;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

/**
 * @implements OptionsInterface
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
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface
     */
    public function withOptions(array $options, int $flags = 0): ElementBuilderInterface
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
        $labelKey = $flags & OptionsInterface::USE_PLAIN_LABEL ? 'plainLabel' : 'label';

        if ($flags & OptionsInterface::INCLUDE_OPTION_DEFAULT) {
            array_unshift($options, [
                'value'   => OptionsInterface::VALUE_DEFAULT,
                $labelKey => sprintf('%s_default', Arr::first($this->prefixes)),
            ]);
        }

        if ($flags & OptionsInterface::INCLUDE_OPTION_NONE) {
            array_unshift($options, [
                'value'   => OptionsInterface::VALUE_DEFAULT,
                $labelKey => sprintf('%s_none', Arr::first($this->prefixes)),
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
        $associativeArray = (Arr::isAssoc($array) ? $array : array_combine($array, $array)) ?? [];

        $options = array_map(function ($value, $key) use ($flags) {
            $usePlainLabel = $flags & OptionsInterface::USE_PLAIN_LABEL;
            $labelKey      = $usePlainLabel ? 'plainLabel' : 'label';

            return [
                'value'   => $key,
                $labelKey => $usePlainLabel ? $value : $this->createOptionLabel($value),
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
