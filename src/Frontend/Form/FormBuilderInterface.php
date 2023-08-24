<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

interface FormBuilderInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface ...$builders
     *
     * @return $this
     */
    public function add(ElementBuilderInterface ...$builders): self;

    /**
     * @param  callable                                                               $callback
     * @param  \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface ...$builders
     *
     * @return $this
     */
    public function addWith(callable $callback, ElementBuilderInterface ...$builders): self;

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface[]
     */
    public function all(): array;

    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    public function build(): FormElementCollection;
}
