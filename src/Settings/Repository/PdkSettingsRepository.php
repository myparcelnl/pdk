<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Repository\StorageRepository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\SettingsManager;

class PdkSettingsRepository extends StorageRepository implements SettingsRepositoryInterface
{
    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
    public function all(): Settings
    {
        return $this->retrieve(SettingsManager::KEY_ALL, function () {
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
        }, false, $this->cache);
    }

    /**
     * @param  string $key
     *
     * @return mixed
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
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function store(string $key, $value): void
    {
        $this->save($key, $value);
    }

    /**
     * @param  Settings|AbstractSettingsModel|SettingsModelCollection $settings
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function storeSettings($settings): void
    {
        if ($settings instanceof Settings) {
            foreach (array_keys($settings->getAttributes()) as $attribute) {
                $this->storeSettings($settings->getAttribute($attribute));
            }
            return;
        }

        $value = $settings instanceof SettingsModelCollection
            ? $this->getSettingsCollectionValue($settings)
            : $settings->toStorableArray();

        $this->save($settings->id, $value);
    }

    protected function getGroup(string $namespace)
    {
        return $this->retrieve($namespace);
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return 'settings_';
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection $settings
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getSettingsCollectionValue(SettingsModelCollection $settings): array
    {
        $id = $settings->first()->id ?? $settings->id;

        /** @var array $existing */
        $existing = $this->get($id) ?? [];

        return array_replace($existing, $settings->toStorableArray());
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    protected function save(string $key, $data)
    {
        $this->cache->delete($this->getKeyPrefix() . SettingsManager::KEY_ALL);

        return parent::save($key, $data);
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings                     $settings
     * @param  \MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection $collection
     * @param  string                                                      $settingsId
     *
     * @return void
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
