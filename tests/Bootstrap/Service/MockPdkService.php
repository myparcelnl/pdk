<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap\Service;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\Contract\MockPdkServiceInterface;
use Throwable;

final class MockPdkService implements MockPdkServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\MockPdk
     */
    private $pdk;

    /**
     * @var callable[]
     */
    private $resets = [];

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
        $oldValue = $this->getOldValue($key);

        $this->pdk->set($key, $value);

        $this->addReset($key, $oldValue);
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
        foreach ($this->resets as $resetCallback) {
            $resetCallback();
        }
    }

    /**
     * @param  string $key
     * @param  mixed  $oldValue
     *
     * @return void
     */
    private function addReset(string $key, $oldValue): void
    {
        $this->resets[] = function () use ($oldValue, $key) {
            $this->pdk->set($key, $oldValue);
        };
    }

    /**
     * @param  string $key
     *
     * @return null|mixed
     */
    private function getOldValue(string $key)
    {
        if (! $this->pdk->has($key)) {
            return null;
        }

        try {
            return $this->pdk->get($key);
        } catch (Throwable $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log(sprintf('Could not retrieve key "%s". Reason: %s', $key, $e->getMessage()));

            return null;
        }
    }
}
