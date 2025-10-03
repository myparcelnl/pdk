<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Logger;

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
}
