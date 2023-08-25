<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use BadMethodCallException;
use InvalidArgumentException;
use MyParcelNL\Pdk\Frontend\Form\Builder\Concern\HasFormOperationBuilderParent;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;

abstract class AbstractFormBuilderCore implements FormOperationBuilderInterface
{
    use HasFormOperationBuilderParent;

    /**
     * @var string[]
     */
    protected $magicMethods = [];

    /**
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        if (in_array($name, $this->magicMethods, true)) {
            return $this->{$name}();
        }

        throw new InvalidArgumentException('Property does not exist');
    }

    public function __isset($name)
    {
        $this->throwOnAccessorMethod();
    }

    public function __set($name, $value)
    {
        $this->throwOnAccessorMethod();
    }

    public function __unset($name)
    {
        $this->throwOnAccessorMethod();
    }

    /**
     * @return mixed
     */
    private function throwOnAccessorMethod()
    {
        throw new BadMethodCallException('Not implemented');
    }
}
