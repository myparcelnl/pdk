<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\HasHooks;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\PlainElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;

abstract class AbstractPlainElement implements PlainElementBuilderInterface
{
    use HasHooks;

    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder
     */
    protected $builder;

    /**
     * @var null|string
     */
    protected $content;

    /**
     * @var null|string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $prefixes = [];

    /**
     * @var mixed
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $props = [];

    abstract protected function getComponent(): string;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return $this
     */
    public function disabledWhen(?string $target = null, $valueOrCallback = null): ElementBuilderInterface
    {
        $this->getBuilder()
            ->disabledWhen($target, $valueOrCallback);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->getProp('name');
    }

    /**
     * @param  string $key
     *
     * @return null|mixed
     */
    public function getProp(string $key)
    {
        return $this->props[$key] ?? null;
    }

    /**
     * @param  string $key
     *
     * @return bool
     */
    public function hasProp(string $key): bool
    {
        return isset($this->props[$key]);
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface
     */
    public function make(): ElementInterface
    {
        return (new PlainElement($this->getComponent(), $this->getProps(), $this->getContent()))
            ->setBuilder($this->builder);
    }

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return $this
     */
    public function visibleWhen(?string $target = null, $valueOrCallback = null): ElementBuilderInterface
    {
        $this->getBuilder()
            ->visibleWhen($target, $valueOrCallback);

        return $this;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function withAttribute(string $key, $value): ElementBuilderInterface
    {
        return $this->withAttributes([$key => $value]);
    }

    /**
     * @param  array $attributes
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface
     */
    public function withAttributes(array $attributes): ElementBuilderInterface
    {
        $this->attributes = array_replace($this->attributes, $attributes);

        return $this;
    }

    /**
     * @param  string $name
     *
     * @return $this
     */
    public function withName(string $name): ElementBuilderInterface
    {
        $this->withProp('name', $name);

        return $this;
    }

    /**
     * @param  string ...$prefixes
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface
     */
    public function withPrefixes(string ...$prefixes): ElementBuilderInterface
    {
        $this->prefixes = $prefixes;

        return $this;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function withProp(string $key, $value): ElementBuilderInterface
    {
        return $this->withProps([$key => $value]);
    }

    /**
     * @param  array $props
     *
     * @return $this
     */
    public function withProps(array $props): ElementBuilderInterface
    {
        $this->props = array_replace($this->props, $props);

        return $this;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder
     */
    protected function getBuilder(): FormOperationBuilder
    {
        if (! isset($this->builder)) {
            $this->builder = new FormOperationBuilder();
        }

        return $this->builder;
    }

    /**
     * @return null|string
     */
    protected function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return array
     */
    protected function getProps(): array
    {
        $this->executeHooks(ElementBuilderInterface::HOOK_PROPS);

        return array_filter(
            array_merge(
                $this->props,
                [
                    '$attributes' => array_filter($this->attributes),
                ]
            )
        );
    }
}

