<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Base\Support\Arrayable;

class PlainElement implements Arrayable
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
     * @param  string $component
     * @param  array  $props
     */
    public function __construct(string $component, array $props = [])
    {
        $this->props     = $props;
        $this->component = $component;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
                '$component' => $this->component,
            ] + $this->props;
    }
}
