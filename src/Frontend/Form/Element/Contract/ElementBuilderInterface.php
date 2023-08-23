<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Contract;

interface ElementBuilderInterface
{
    public const HOOK_PROPS = 'props';

    /**
     * @param  callable $callback
     *
     * @return $this
     */
    public function builder(callable $callback): ElementBuilderInterface;

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
    public function visibleWhen(?string $target = null, $valueOrCallback = null): ElementBuilderInterface;

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function withAttribute(string $key, $value): ElementBuilderInterface;

    /**
     * @param  array $attributes
     *
     * @return $this
     */
    public function withAttributes(array $attributes): ElementBuilderInterface;

    /**
     * @param  string ...$prefixes
     *
     * @return $this
     */
    public function withPrefixes(string ...$prefixes): ElementBuilderInterface;

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function withProp(string $key, $value): ElementBuilderInterface;

    /**
     * @param  array $props
     *
     * @return $this
     */
    public function withProps(array $props): ElementBuilderInterface;
}
