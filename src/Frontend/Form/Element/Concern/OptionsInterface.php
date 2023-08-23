<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Concern;

use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

/**
 * @see \MyParcelNL\Pdk\Frontend\Form\Element\Concern\HasOptions
 */
interface OptionsInterface
{
    public const USE_PLAIN_LABEL        = 1;
    public const INCLUDE_OPTION_NONE    = 2;
    public const INCLUDE_OPTION_DEFAULT = 4;
    public const VALUE_DEFAULT          = -1;

    public function withOptions(array $options, int $flags = 0): ElementBuilderInterface;
}
