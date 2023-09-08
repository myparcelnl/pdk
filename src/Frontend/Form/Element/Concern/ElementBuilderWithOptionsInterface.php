<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Concern;

use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

/**
 * @see \MyParcelNL\Pdk\Frontend\Form\Element\Concern\HasOptions
 */
interface ElementBuilderWithOptionsInterface extends ElementBuilderInterface
{
    public const USE_PLAIN_LABEL = 1;
    public const ADD_NONE        = 2;
    public const ADD_DEFAULT     = 4;
    public const VALUE_DEFAULT   = -1;
    public const SORT_ASC        = 'asc';
    public const SORT_DESC       = 'desc';

    /**
     * @param  array $options
     * @param  int   $flags
     *
     * @return $this
     */
    public function withOptions(array $options, int $flags = 0): ElementBuilderWithOptionsInterface;

    /**
     * @param  string $sort
     *
     * @return $this
     */
    public function withSort(string $sort): ElementBuilderWithOptionsInterface;
}
