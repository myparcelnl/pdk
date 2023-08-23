<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;

final class FormBuilder
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
    public function add(ElementBuilderInterface ...$builders): self
    {
        $this->elements = array_merge(
            $this->elements,
            array_map(function (ElementBuilderInterface $builder) {
                return $builder->withPrefixes(...$this->prefixes);
            }, $builders)
        );

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
     * @return array
     */
    public function build(): array
    {
        return array_map(
            static function (ElementBuilderInterface $builder) {
                return $builder->make();
            },
            $this->elements
        );
    }
}
