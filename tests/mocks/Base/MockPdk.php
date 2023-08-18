<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Contract\MockServiceInterface;

final class MockPdk extends Pdk implements MockServiceInterface
{
    private $previous = [];

    public function reset(): void
    {
        foreach ($this->previous as $key => $value) {
            $this->container->set($key, $value);
        }
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        if ($this->container->has($key)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->previous[$key] = $this->container->get($key);
        }

        $this->container->set($key, $value);
    }
}
