<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings;

use MyParcelNL\Pdk\Base\Support\Helpers;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;

class SettingsManager
{
    /**
     * @var \MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository
     */
    protected $repository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Model\Settings
     */
    protected $settings;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository $repository
     */
    public function __construct(AbstractSettingsRepository $repository)
    {
        $this->repository = $repository;
        $this->settings   = $this->repository->getSettings();
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
    public function all(): Settings
    {
        return $this->settings;
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        $keyParts = explode('.', $key);
        $first    = array_shift($keyParts);

        return (new Helpers())->data_get($this->settings[$first], $keyParts);
    }

    /**
     * @return void
     */
    public function persist(): void
    {
        $this->repository->store($this->settings);
    }
}
