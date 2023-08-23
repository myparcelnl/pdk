<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

final class FormBuilder implements FormBuilderInterface
{
    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface[]
     */
    private $elements = [];

    /**
     * @var array
     */
    private $prefixes;

    /**
     * @param  array $prefixes
     */
    public function __construct(array $prefixes = [])
    {
        $this->prefixes = $prefixes;
    }

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface ...$builders
     *
     * @return $this
     */
    public function add(ElementBuilderInterface ...$builders): FormBuilderInterface
    {
        foreach ($builders as $builder) {
            $this->addBuilder($builder);
        }

        return $this;
    }

    /**
     * @param  callable                                                               $callback
     * @param  \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface ...$builders
     *
     * @return $this
     */
    public function addWith(callable $callback, ElementBuilderInterface ...$builders): FormBuilderInterface
    {
        foreach ($builders as $builder) {
            $callback($builder);

            $this->addBuilder($builder);
        }

        return $this;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface[]
     */
    public function all(): array
    {
        return $this->elements;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    public function build(): FormElementCollection
    {
        return new FormElementCollection(
            array_map(
                static function (ElementBuilderInterface $builder) {
                    return $builder->make();
                },
                $this->elements
            )
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface $builder
     *
     * @return void
     */
    private function addBuilder(ElementBuilderInterface $builder): void
    {
        $this->elements[] = $builder->withPrefixes(...$this->prefixes);
    }
}
