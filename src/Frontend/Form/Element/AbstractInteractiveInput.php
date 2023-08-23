<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;

abstract class AbstractInteractiveInput extends AbstractPlainElement implements InteractiveElementBuilderInterface
{
    /**
     * @param  string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param  callable $callable
     *
     * @return $this
     */
    public function afterUpdate(callable $callable): InteractiveElementBuilderInterface
    {
        $this->getBuilder()
            ->afterUpdate($callable);

        return $this;
    }

    /**
     * @param  callable $callback
     *
     * @return $this
     */
    public function build(callable $callback): InteractiveElementBuilderInterface
    {
        $callback($this->getBuilder());

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface
     */
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

    /**
     * @param  string ...$parts
     *
     * @return string
     */
    protected function createLabel(string ...$parts): string
    {
        return implode('_', array_merge($this->prefixes, $parts));
    }
}
