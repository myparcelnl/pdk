<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use MyParcelNL\Pdk\Base\Container;
use MyParcelNL\Sdk\src\Support\Helpers;

trait HidesAttributes
{
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = [];

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * Get the visible attributes for the model.
     *
     * @return array
     */
    public function getVisible(): array
    {
        return $this->visible;
    }

    /**
     * Make the given, typically visible, attributes hidden.
     *
     * @param  array|string|null $attributes
     *
     * @return static
     */
    public function makeHidden($attributes): self
    {
        $this->hidden = array_merge(
            $this->hidden,
            is_array($attributes) ? $attributes : func_get_args()
        );

        return $this;
    }

    /**
     * Make the given, typically visible, attributes hidden if the given truth test passes.
     *
     * @param  bool|Closure      $condition
     * @param  array|string|null $attributes
     *
     * @return static
     * @throws \Exception
     */
    public function makeHiddenIf($condition, $attributes): self
    {
        return Container::getInstance()
            ->get(Helpers::class)
            ->value($condition) ? $this->makeHidden($attributes) : $this;
    }

    /**
     * Make the given, typically hidden, attributes visible.
     *
     * @param  array|string|null $attributes
     *
     * @return static
     */
    public function makeVisible($attributes): self
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->hidden = array_diff($this->hidden, $attributes);

        if (! empty($this->visible)) {
            $this->visible = array_merge($this->visible, $attributes);
        }

        return $this;
    }

    /**
     * Make the given, typically hidden, attributes visible if the given truth test passes.
     *
     * @param  bool|Closure      $condition
     * @param  array|string|null $attributes
     *
     * @return static
     */
    public function makeVisibleIf($condition, $attributes): self
    {
        return (new Helpers())->value($condition) ? $this->makeVisible($attributes) : $this;
    }

    /**
     * Set the hidden attributes for the model.
     *
     * @param  array $hidden
     *
     * @return static
     */
    public function setHidden(array $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Set the visible attributes for the model.
     *
     * @param  array $visible
     *
     * @return static
     */
    public function setVisible(array $visible): self
    {
        $this->visible = $visible;

        return $this;
    }
}
