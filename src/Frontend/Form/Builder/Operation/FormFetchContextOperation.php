<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use InvalidArgumentException;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;

final class FormFetchContextOperation extends AbstractFormOperation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface $parent
     * @param  string                                                                       $id
     */
    public function __construct(FormOperationBuilderInterface $parent, string $id)
    {
        parent::__construct($parent);

        if (! in_array($id, Context::ALL, true)) {
            throw new InvalidArgumentException('Value must be a valid context');
        }

        $this->id = $id;
    }

    /**
     * @return array
     */
    protected function createArray(): array
    {
        return ['$id' => $this->id] + parent::createArray();
    }

    protected function getOperationKey(): string
    {
        return '$fetchContext';
    }
}
