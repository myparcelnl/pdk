<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Concern\HasFormOperationBuilderParent;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\BuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;

/**
 * @property FormOperationBuilderInterface $then
 */
abstract class AbstractFormBuilderCore implements BuilderInterface
{
    use HasFormOperationBuilderParent;

    /**
     * @var string[]
     */
    protected $magicMethods = ['then'];

    /**
     * @param $name
     *
     * @return null
     * @noinspection MultipleReturnStatementsInspection
     */
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

    protected function then(): FormOperationBuilderInterface
    {
        return $this->getRoot();
    }
}
