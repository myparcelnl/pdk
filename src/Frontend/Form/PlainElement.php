<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

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
     * @var null|string
     */
    private $content;

    /**
     * @param  string      $component
     * @param  null|array  $props
     * @param  null|string $content
     */
    public function __construct(string $component, ?array $props = [], string $content = null)
    {
        $this->props     = $props ?? [];
        $this->component = $component;
        $this->content   = $content;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge([
            '$component' => $this->component,
            '$slot'      => $this->content,
            '$wrapper'   => false,
        ], $this->props);
    }
}
