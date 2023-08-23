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
     * @param  callable $callback
     *
     * @return $this
     */
    public function build(callable $callback): self;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return $this
     */
    public function readOnlyWhen(?string $target = null, $valueOrCallback = null): self;
}
