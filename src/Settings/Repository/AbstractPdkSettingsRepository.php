<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\Settings;

abstract class AbstractPdkSettingsRepository extends Repository implements PdkSettingsRepositoryInterface
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
    abstract public function store(string $key, $value): void;

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
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
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings $settings
     *
     * @return void
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
     * @return void
     */
    public function storeSettings($settings): void
    {
        if (! $settings instanceof SettingsModelCollection) {
            $this->store($this->createSettingsKey($settings->id), $settings->toStorableArray());

            return;
        }

        /** @var array $existing */
        $id       = $settings->first()->id ?? $settings->id;
        $existing = $this->get($this->createSettingsKey($id)) ?? [];

        $this->store($this->createSettingsKey($settings->id), array_replace($existing, $settings->toStorableArray()));
    }

    /**
     * @param  string $input
     *
     * @return string
     */
    protected function createSettingsKey(string $input): string
    {
        return Pdk::get('createSettingsKey')($input);
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
            return $this->get($this->createSettingsKey(implode('.', [$id, $key])));
        }, $keys);

        $attributes = array_combine($keys, $values);

        $settings->setAttribute($id, $model->fill($attributes));

        return $settings;
    }
}
