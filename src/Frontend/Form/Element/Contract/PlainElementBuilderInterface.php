<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Contract;

interface PlainElementBuilderInterface extends ElementBuilderInterface
{
    public function withName(string $name): ElementBuilderInterface;
}
