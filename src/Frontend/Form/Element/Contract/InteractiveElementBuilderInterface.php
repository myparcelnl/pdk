<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Contract;

interface InteractiveElementBuilderInterface extends ElementBuilderInterface
{
    /**
     * @param  callable $callable
     *
     * @return $this
     */
    public function afterUpdate(callable $callable): self;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return $this
     */
    public function readOnlyWhen(?string $target = null, $valueOrCallback = null): self;
}
