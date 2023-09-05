<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Contract;

interface ElementBuilderInterface
{
    public const HOOK_PROPS = 'props';

    /**
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface
     */
    public function make(): ElementInterface;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return $this
     */
    public function visibleWhen(?string $target = null, $valueOrCallback = null): self;

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function withAttribute(string $key, $value): self;

    /**
     * @param  array $attributes
     *
     * @return $this
     */
    public function withAttributes(array $attributes): self;

    /**
     * @param  string ...$prefixes
     *
     * @return $this
     */
    public function withPrefixes(string ...$prefixes): self;

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function withProp(string $key, $value): self;

    /**
     * @param  array $props
     *
     * @return $this
     */
    public function withProps(array $props): self;
}
