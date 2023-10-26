<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface;

/**
 * @todo merge into AbstractPlainElement when forms are converted
 */
class PlainElement implements ElementInterface
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
     * @var null|\MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder
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
    public function builder(callable $callback): ElementInterface
    {
        if (! isset($this->builder)) {
            $this->builder = new FormOperationBuilder();
        }

        $callback($this->builder);

        return $this;
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder $builder
     *
     * @return $this
     */
    public function setBuilder(?FormOperationBuilder $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
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
