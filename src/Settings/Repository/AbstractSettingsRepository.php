<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Arrayable;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\Settings;

abstract class AbstractSettingsRepository extends ApiRepository implements SettingsRepositoryInterface
{
    /**
     * @param  string $namespace
     *
     * @return array|\MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel
     */
    abstract public function getGroup(string $namespace);

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    abstract protected function store(string $key, $value): void;

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function all(): Settings
    {
        return $this->retrieve('all', function () {
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
     * @param  string $key
     *
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
     * @param  AbstractSettingsModel|SettingsModelCollection $settings
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function storeSettings($settings): void
    {
        if (! $settings instanceof SettingsModelCollection) {
            $this->store($settings->id, $settings->toStorableArray());
            return;
        }

        /** @var array $existing */
        $existing = $this->get($settings->id);

        $this->store($settings->id, array_replace($existing, $settings->toStorableArray()));
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return 'settings_';
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings                     $settings
     * @param  \MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection $collection
     * @param  string                                                      $settingsId
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function updateSettingsFromCollection(
        Settings                $settings,
        SettingsModelCollection $collection,
        string                  $settingsId
    ): Settings {
        $category = $this->get($settingsId) ?? [];

        foreach ($category as $key => $item) {
            $values = ['id' => $key] + $this->get("$settingsId.$key");

            $collection->offsetSet($key, $values);
        }

        $settings->setAttribute($settingsId, $collection);

        return $settings;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings              $settings
     * @param  \MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel $model
     * @param  null|string                                          $id
     *
     * @return Settings
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function updateSettingsFromModel(
        Settings              $settings,
        AbstractSettingsModel $model,
        string                $id = null
    ): Settings {
        $id = $id ?? $model->id;

        $keys   = array_keys(Arr::except($model->getAttributes(), 'id'));
        $values = array_map(function ($key) use ($id) {
            return $this->get(implode('.', [$id, $key]));
        }, $keys);

        $attributes = array_combine($keys, $values);

        $settings->setAttribute($id, $model->fill($attributes));

        return $settings;
    }
}
