<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Concern;

trait HasHooks
{
    /**
     * @var array
     */
    protected $hooks = [];

    /**
     * @param  string   $hook
     * @param  callable $callback
     *
     * @return $this
     */
    protected function addHook(string $hook, callable $callback): self
    {
        if (! isset($this->hooks[$hook])) {
            $this->hooks[$hook] = [];
        }

        $this->hooks[$hook][] = $callback;

        return $this;
    }

    /**
     * @param  string $hook
     * @param  mixed  ...$args
     *
     * @return void
     */
    protected function executeHooks(string $hook, ...$args): void
    {
        if (! isset($this->hooks[$hook])) {
            return;
        }

        while ($callback = array_shift($this->hooks[$hook])) {
            $callback(...$args);
        }
    }
}
