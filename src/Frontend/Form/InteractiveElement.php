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

    /**
     * @param  string $name
     * @param  string $component
     * @param  array  $props
     */
    public function __construct(string $name, string $component, array $props = [])
    {
        parent::__construct($component, array_merge(['$wrapper' => null], $props));
        $this->name = $name;
    }

    /**
     * @param  null|int $flags
     *
     * @return string[]
     */
    public function toArray(?int $flags = null): array
    {
        return [
                'name' => $this->name,
            ] + parent::toArray($flags);
    }
}
