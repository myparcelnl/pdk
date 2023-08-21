<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap\Service;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\Contract\MockPdkServiceInterface;

final class MockPdkService implements MockPdkServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\MockPdk
     */
    private $pdk;

    /**
     * @param  \MyParcelNL\Pdk\Base\MockPdk $pdk
     */
    public function __construct(PdkInterface $pdk)
    {
        $this->pdk = $pdk;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function override(string $key, $value): void
    {
        $this->pdk->set($key, $value);
    }

    /**
     * @param  array $config
     *
     * @return void
     */
    public function overrideMany(array $config): void
    {
        foreach ($config as $key => $value) {
            $this->override($key, $value);
        }
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->pdk->reset();
    }
}
