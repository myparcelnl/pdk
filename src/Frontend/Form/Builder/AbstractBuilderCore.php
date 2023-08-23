<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\BuilderInterface;
use RuntimeException;

/**
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder $then
 */
abstract class AbstractBuilderCore implements BuilderInterface
{
    /**
     * @var string[]
     */
    protected $magicMethods = ['then'];

    /**
     * @var BuilderInterface
     */
    protected $parent;

    /**
     * @param  null|\MyParcelNL\Pdk\Frontend\Form\Builder\Contract\BuilderInterface $parent
     */
    public function __construct(?BuilderInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    abstract protected function createArray(): array;

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (in_array($name, $this->magicMethods, true)) {
            return $this->{$name}();
        }

        return null;
    }

    public function __isset($name)
    {
        if (property_exists($this, $name)) {
            return null !== $this->{$name};
        }

        return in_array($name, $this->magicMethods, true);
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
    }

    public function __unset($name)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = null;
        }
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $root = $this->getRoot();

        $array = $root->createArray();

        return array_filter(
            $array,
            static function ($value) {
                return ! empty($value);
            }
        );
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder
     */
    protected function getRoot(): FormOperationBuilder
    {
        $root = $this;

        while ($root->parent) {
            $root = $root->parent;
        }

        if (! $root instanceof FormOperationBuilder) {
            throw new RuntimeException(sprintf('Root is not a %s', FormOperationBuilder::class));
        }

        return $root;
    }

    protected function then(): FormOperationBuilder
    {
        return $this->getRoot();
    }
}
