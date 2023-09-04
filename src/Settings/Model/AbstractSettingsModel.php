<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Platform;

/**
 * Settings model.
 *
 * @property string $id
 */
abstract class AbstractSettingsModel extends Model implements StorableArrayable
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

        $this->setPlatformDefaults();
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        return Arr::except($this->toArrayWithoutNull(), 'id');
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

            $defaultValue = Platform::get(sprintf('settings.defaults.%s.%s', $this->id, $key));

            if (null === $defaultValue) {
                continue;
            }

            $this->setAttribute($key, $defaultValue);
        }
    }
}
