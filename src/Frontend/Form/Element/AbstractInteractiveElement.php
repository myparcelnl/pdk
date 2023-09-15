<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;

abstract class AbstractInteractiveElement extends AbstractPlainElement implements InteractiveElementBuilderInterface
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return $this
     */
    public function afterUpdate(callable $callable): InteractiveElementBuilderInterface
    {
        $this->getBuilder()
            ->afterUpdate($callable);

        return $this;
    }

    public function make(): ElementInterface
    {
        return (new InteractiveElement($this->name, $this->getComponent(), $this->getProps()))
            ->setBuilder($this->builder);
    }

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return $this
     */
    public function readOnlyWhen(?string $target = null, $valueOrCallback = null): InteractiveElementBuilderInterface
    {
        $this->getBuilder()
            ->readOnlyWhen($target, $valueOrCallback);

        return $this;
    }

    protected function createLabel(string ...$parts): string
    {
        return implode('_', array_merge($this->prefixes, $parts));
    }
}
