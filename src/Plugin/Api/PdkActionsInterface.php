<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api;

interface PdkActionsInterface
{
    /**
     * Return an array of allowed actions.
     *
     * @return string[]
     */
    public function getActions(): array;
}
