<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

interface FormBuilderInterface
{
    /**
     * @return $this
     */
    public function add(ElementBuilderInterface ...$builders): self;

    /**
     * @return $this
     */
    public function addWith(callable $callback, ElementBuilderInterface ...$builders): self;

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface[]
     */
    public function all(): array;

    public function build(): FormElementCollection;
}
