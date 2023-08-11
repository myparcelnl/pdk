<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;

class PlainElement implements Arrayable
{
    /**
     * @var string
     */
    public $component;

    /**
     * @var array
     */
    public $props;

    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder
     */
    private $builder;

    /**
     * @var null|string
     */
    private $content;

    /**
     * @param  string      $component
     * @param  null|array  $props
     * @param  null|string $content
     */
    public function __construct(string $component, ?array $props = [], string $content = null)
    {
        $this->props     = $props ?? [];
        $this->component = $component;
        $this->content   = $content;
    }

    /**
     * @param  callable $callback
     *
     * @return $this
     */
    public function builder(callable $callback): self
    {
        if (! isset($this->builder)) {
            $this->builder = new FormOperationBuilder();
        }

        $callback($this->builder);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return Utils::filterNull(
            array_merge(
                $this->builder ? ['$builders' => $this->builder->build()] : [],
                [
                    '$component' => $this->component,
                    '$slot'      => $this->content,
                    '$wrapper'   => false,
                ],
                $this->props
            )
        );
    }
}
