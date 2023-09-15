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
    private array $elements = [];

    public function __construct(private readonly array $prefixes = [])
    {
    }

    /**
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

    public function build(): FormElementCollection
    {
        return new FormElementCollection(
            array_map(
                static fn(ElementBuilderInterface $builder) => $builder->make(),
                $this->elements
            )
        );
    }

    private function addBuilder(ElementBuilderInterface $builder): void
    {
        $this->elements[] = $builder->withPrefixes(...$this->prefixes);
    }
}
