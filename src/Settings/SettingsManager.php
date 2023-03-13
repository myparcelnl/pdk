<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings;

use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;

class SettingsManager
{
    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface
     */
    protected $repository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Model\Settings
     */
    protected $settings;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface $repository
     */
    public function __construct(SettingsRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->settings   = $this->repository->all();
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
    public function all(): Settings
    {
        return $this->settings;
    }

    /**
     * @param  string      $key
     * @param  null|string $namespace
     *
     * @return mixed
     */
    public function get(string $key, ?string $namespace = null)
    {
        if ($namespace) {
            $key = sprintf('%s.%s', $namespace, $key);
        }

        return $this->repository->get($key);
    }
}
