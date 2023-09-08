<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Concern;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

/**
 * @see ElementBuilderWithOptionsInterface
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
     * @param  string $sort
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface
     */
    public function withSort(string $sort): ElementBuilderWithOptionsInterface
    {
        return $this->withProp('sort', $sort);
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
                'label' => sprintf('%s_default', Arr::first($this->prefixes)),
            ]);
        }

        if ($flags & ElementBuilderWithOptionsInterface::ADD_NONE) {
            array_unshift($options, [
                'value' => ElementBuilderWithOptionsInterface::VALUE_DEFAULT,
                'label' => sprintf('%s_none', Arr::first($this->prefixes)),
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
