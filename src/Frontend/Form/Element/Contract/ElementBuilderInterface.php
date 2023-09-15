<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Contract;

interface ElementBuilderInterface
{
    public const HOOK_PROPS = 'props';

    public function getName(): ?string;

    public function make(): ElementInterface;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return $this
     */
    public function visibleWhen(?string $target = null, $valueOrCallback = null): self;

    /**
     * @param  mixed $value
     *
     * @return $this
     */
    public function withAttribute(string $key, $value): self;

    /**
     * @return $this
     */
    public function withAttributes(array $attributes): self;

    /**
     * @return $this
     */
    public function withPrefixes(string ...$prefixes): self;

    /**
     * @param  mixed $value
     *
     * @return $this
     */
    public function withProp(string $key, $value): self;

    /**
     * @return $this
     */
    public function withProps(array $props): self;
}
