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
     * @param  string $key
     *
     * @return null|mixed
     */
    public function getProp(string $key);

    /**
     * @param  string $key
     *
     * @return bool
     */
    public function hasProp(string $key): bool;

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
