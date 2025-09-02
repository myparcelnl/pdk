<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;

/**
 * Settings model.
 *
 * @property string $id
 */
abstract class AbstractSettingsModel extends Model
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->casts['id'] = 'string';

        parent::__construct($data);

        if (! $this->id) {
            Logger::error('Settings model must have an id.', ['class' => static::class]);
        }
    }

    /**
     * @return array<string>
     */
    public function all(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return Arr::except(parent::toStorableArray(), 'id');
    }

    /**
     * Set default values for settings that have a default of null, if present in platform configuration.
     *
     * @return void
     */
    protected function setPlatformDefaults(): void
    {
        foreach ($this->getAttributes() as $key => $value) {
            if (null !== $value) {
                continue;
            }

            // Get default value from proposition config if available
            $defaultValue = null;
            try {
                $propositionService = Pdk::get(PropositionService::class);
                $proposition = $propositionService->getPropositionConfig();
                $platformConfig = $propositionService->mapToPlatformConfig($proposition);

                $defaultValue = $platformConfig['defaultSettings'][$this->id][$key] ?? null;
            } catch (\Exception $e) {
                // Fallback to null if proposition config is not available
                $defaultValue = null;
            }

            if (null === $defaultValue) {
                continue;
            }

            $this->setAttribute($key, $defaultValue);
        }
    }
}
