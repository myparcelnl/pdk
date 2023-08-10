<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Concern;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\BuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;
use RuntimeException;

trait HasFormOperationBuilderParent
{
    /**
     * @var BuilderInterface
     */
    protected $parent;

    /**
     * @param  null|\MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface $parent
     */
    public function __construct(?FormOperationBuilderInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this
            ->getRoot()
            ->build();
    }

    /**
     * @return FormOperationBuilderInterface|null
     */
    public function getParent(): ?FormOperationBuilderInterface
    {
        return $this->parent;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface
     */
    protected function getRoot(): FormOperationBuilderInterface
    {
        $root = $this;

        while (null !== $root->getParent()) {
            $root = $root->getParent();
        }

        if (! $root instanceof FormOperationBuilderInterface) {
            throw new RuntimeException(sprintf('Root is not a %s', FormOperationBuilderInterface::class));
        }

        return $root;
    }
}

