<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

/**
 * @todo merge into AbstractInteractiveElement when forms are converted
 */
class InteractiveElement extends PlainElement
{
    /**
     * @var string
     */
    public $name;

    public function __construct(string $name, string $component, array $props = [])
    {
        parent::__construct($component, array_merge(['$wrapper' => null], $props));
        $this->name = $name;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
                'name' => $this->name,
            ] + parent::toArray();
    }
}
