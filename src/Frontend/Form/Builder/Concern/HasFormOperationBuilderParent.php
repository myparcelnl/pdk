<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Concern;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\RootFormOperationBuilderInterface;

trait HasFormOperationBuilderParent
{
    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface
     */
    protected $parent;

    /**
     * @param  null|\MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface $parent
     */
    public function __construct(?FormOperationBuilderInterface $parent)
    {
        $this->parent = $parent;
    }

    public function build(): array
    {
        return $this
            ->getRoot()
            ->build();
    }

    public function getParent(): ?FormOperationBuilderInterface
    {
        return $this->parent;
    }

    protected function getRoot(): RootFormOperationBuilderInterface
    {
        $root = $this->parent;

        while (null !== $root->getParent()) {
            $root = $root->getParent();
        }

        return $root;
    }
}

