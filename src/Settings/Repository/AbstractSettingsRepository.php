<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\Settings;

abstract class AbstractSettingsRepository extends Repository implements SettingsRepositoryInterface
{
    /**
     * @return array|\MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel
     */
    abstract public function getGroup(string $namespace);

    /**
     * @param  mixed $value
     */
    abstract public function store(string $key, $value): void;

    public function all(): Settings
    {
        return $this->retrieveAll(function () {
            $settings = new Settings();

            foreach ($settings->getAttributes() as $settingsId => $settingsModelOrCollection) {
                if ($settingsModelOrCollection instanceof AbstractSettingsModel) {
                    $settings = $this->updateSettingsFromModel($settings, $settingsModelOrCollection);
                }

                if ($settingsModelOrCollection instanceof SettingsModelCollection) {
                    $settings = $this->updateSettingsFromCollection($settings, $settingsModelOrCollection, $settingsId);
                }
            }

            return $settings;
        });
    }

    /**
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function get(string $key)
    {
        $parts = explode('.', $key);
        $group = $this->getGroup(array_shift($parts));

        if (empty($parts)) {
            return $group;
        }

        if ($group instanceof Arrayable) {
            $group = $group->toArray();
        }

        return Arr::get($group, implode('.', $parts));
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function storeAllSettings(Settings $settings): void
    {
        foreach (array_keys($settings->getAttributes()) as $attribute) {
            $this->storeSettings($settings->getAttribute($attribute));
        }
    }

    /**
     * @param  AbstractSettingsModel|SettingsModelCollection $settings
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function storeSettings($settings): void
    {
        if (! $settings instanceof SettingsModelCollection) {
            $this->store($this->createSettingsKey($settings->id), $settings->toStorableArray());

            return;
        }

        $id       = $settings->first()->id ?? $settings->id;
        $existing = $this->get($this->createSettingsKey($id)) ?? [];

        $this->store($this->createSettingsKey($settings->id), array_replace($existing, $settings->toStorableArray()));
    }

    protected function createSettingsKey(string $input): string
    {
        return Pdk::get('createSettingsKey')($input);
    }

    protected function getKeyPrefix(): string
    {
        return 'settings_';
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function updateSettingsFromCollection(
        Settings                $settings,
        SettingsModelCollection $collection,
        string                  $settingsId
    ): Settings {
        $category = $this->get($this->createSettingsKey($settingsId)) ?? [];

        foreach ($category as $key => $item) {
            $values = ['id' => $key] + $this->get($this->createSettingsKey("$settingsId.$key"));

            $collection->offsetSet($key, $values);
        }

        $settings->setAttribute($settingsId, $collection);

        return $settings;
    }

    /**
     * @param  null|string $id
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function updateSettingsFromModel(
        Settings              $settings,
        AbstractSettingsModel $model,
        string                $id = null
    ): Settings {
        $id ??= $model->id;

        $keys   = array_keys(Arr::except($model->getAttributes(), 'id'));
        $values = array_map(fn($key) => $this->get($this->createSettingsKey(implode('.', [$id, $key]))), $keys);

        $attributes = array_combine($keys, $values);

        $settings->setAttribute($id, $model->fill($attributes));

        return $settings;
    }
}
