<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\SdkModelHelper;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ModelInterface;
use MyParcelNL\Sdk\Client\Generated\CoreApi\ObjectSerializer;

/**
 * Base class for PDK Models that are backed by a single SDK generated ModelInterface instance.
 *
 * SDK generated models (e.g. from OpenAPI codegen) implement ModelInterface and use a different internal structure
 * ($container, static getters/setters/attributeMap) than PDK Models ($attributes, HasAttributes trait). This class
 * bridges the two systems so all SDK model properties are accessible directly on the PDK model.
 *
 * Usage:
 * 1. Extend this class instead of Model.
 * 2. Declare `protected $sdkModelClass = SomeSdkModel::class;` pointing to the ModelInterface implementation.
 *    This is the only requirement — the SDK model is discovered and initialised automatically.
 * 3. Optionally declare native `$attributes` and `$casts` for PDK-only properties on top. Native PDK attributes always take precedence over SDK model properties.
 */
abstract class SdkBackedModel extends Model
{
    /**
     * The SDK ModelInterface class this model is backed by.
     * Subclasses must override this.
     *
     * @var string|null
     */
    protected $sdkModelClass = null;

    /**
     * The SDK model instance backing this model.
     * Lazily initialised on the first write to an SDK property.
     *
     * @var object|null
     */
    private $sdkModel = null;

    /**
     * Getter map cached per class: ['camelCaseKey' => 'getterMethodName', ...]
     *
     * @var array<string, array<string, string>>
     */
    private static $sdkGetterMapCache = [];

    /**
     * Setter map cached per class: ['camelCaseKey' => 'setterMethodName', ...]
     *
     * @var array<string, array<string, string>>
     */
    private static $sdkSetterMapCache = [];

    /**
     * Return the underlying SDK model instance, or null if not yet initialised.
     *
     * @return object|null
     */
    public function getSdkModel(): ?object
    {
        return $this->sdkModel;
    }

    /**
     * Pass non-native keys through setAttribute before handing native data to parent.
     * All routing logic lives in setAttribute — this just ensures fill() sees SDK keys too.
     *
     * @param  array|null $data
     */
    public function __construct(?array $data = null)
    {
        if ($this->sdkModelClass !== null && $data !== null) {
            $nativeKeys    = array_flip(array_keys($this->attributes));
            $nonNativeData = array_diff_key($data, $nativeKeys);

            foreach ($nonNativeData as $key => $value) {
                $this->setAttribute($key, $value);
            }

            $data = array_intersect_key($data, $nativeKeys);
        }

        parent::__construct($data);
    }

    /**
     * Override getAttribute to proxy unknown keys to the SDK model.
     * Native PDK attributes take priority.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        $key = Utils::changeCase($key);

        if (array_key_exists($key, $this->attributes)) {
            return parent::getAttribute($key);
        }

        if ($this->sdkModel !== null) {
            $getterMap = $this->getSdkGetterMap();

            if (isset($getterMap[$key])) {
                return $this->sdkModel->{$getterMap[$key]}();
            }
        }

        return null;
    }

    /**
     * Override setAttribute as the single routing point for all incoming data.
     *
     * Routing order:
     * 1. Native PDK key  → parent::setAttribute
     * 2. Direct SDK model instance → stored as the backing model
     * 3. Known SDK setter key → lazy-init backing model if needed, then proxy to its setter
     * 4. Anything else → parent::setAttribute (dynamic attribute)
     *
     * Because Model::fill() calls setAttribute() for every key in $data, no constructor
     * override is needed — all routing happens here automatically.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return self
     */
    public function setAttribute(string $key, $value): self
    {
        $key = Utils::changeCase($key);

        if (array_key_exists($key, $this->attributes) || $this->isGuarded($key)) {
            parent::setAttribute($key, $value);
            return $this;
        }

        if ($this->sdkModelClass !== null && $value instanceof $this->sdkModelClass) {
            $this->sdkModel = $value;

            return $this;
        }

        if ($this->sdkModelClass !== null) {
            $setterMap = $this->getSdkSetterMap();

            if (isset($setterMap[$key])) {
                if ($this->sdkModel === null) {
                    $this->sdkModel = new $this->sdkModelClass();
                }

                $openApiTypes = $this->sdkModelClass::openAPITypes();
                $type         = $openApiTypes[SdkModelHelper::toOpenApiKey($key)] ?? null;

                if ($type !== null && ! $this->isAlreadyHydrated($value, $type)) {
                    $value = ObjectSerializer::deserialize($value, $type);
                }

                $this->sdkModel->{$setterMap[$key]}($value);

                return $this;
            }
        }

        parent::setAttribute($key, $value);
        return $this;
    }

    /**
     * Override attributesToArray to merge SDK model properties at root level.
     * PDK native attributes take priority over SDK properties.
     *
     * @param  null|int $flags
     *
     * @return array
     */
    public function attributesToArray(?int $flags = null): array
    {
        $pdkAttributes = parent::attributesToArray($flags);

        if ($this->sdkModel === null) {
            return $pdkAttributes;
        }

        $sdkData = SdkModelHelper::toArray($this->sdkModel);

        if ($flags & Arrayable::CASE_SNAKE || $flags & Arrayable::CASE_KEBAB || $flags & Arrayable::CASE_STUDLY) {
            $sdkData = Utils::changeArrayKeysCase($sdkData, $flags);
        }

        return array_merge($sdkData, $pdkAttributes);
    }

    /**
     * Get the getter map for $sdkModelClass, building and caching it on first access.
     * Uses the class name directly — no instance required.
     *
     * @return array<string, string>
     */
    private function getSdkGetterMap(): array
    {
        if ($this->sdkModelClass === null) {
            return [];
        }

        if (! isset(self::$sdkGetterMapCache[$this->sdkModelClass])) {
            self::$sdkGetterMapCache[$this->sdkModelClass] = SdkModelHelper::buildGetterMap($this->sdkModelClass);
        }

        return self::$sdkGetterMapCache[$this->sdkModelClass];
    }

    /**
     * Get the setter map for $sdkModelClass, building and caching it on first access.
     * Uses the class name directly — no instance required.
     *
     * @return array<string, string>
     */
    private function getSdkSetterMap(): array
    {
        if ($this->sdkModelClass === null) {
            return [];
        }

        if (! isset(self::$sdkSetterMapCache[$this->sdkModelClass])) {
            self::$sdkSetterMapCache[$this->sdkModelClass] = SdkModelHelper::buildSetterMap($this->sdkModelClass);
        }

        return self::$sdkSetterMapCache[$this->sdkModelClass];
    }

    /**
     * Returns true when $value is already a hydrated SDK model (or array of models) that should be passed
     * through to the setter as-is. Passing already-hydrated ModelInterface instances to ObjectSerializer::deserialize
     * would break them because the serializer accesses dynamic public properties, which SDK models don't expose.
     *
     * @param  mixed  $value
     * @param  string $type  openAPI type string, e.g. '\Ns\SomeModel' or '\Ns\SomeModel[]'
     *
     * @return bool
     */
    private function isAlreadyHydrated($value, string $type): bool
    {
        // openAPI array types are suffixed with '[]', e.g. '\Ns\SomeModel[]'
        if ('[]' === substr($type, -2)) {
            return is_array($value) && ! empty($value) && reset($value) instanceof ModelInterface;
        }

        return $value instanceof ModelInterface;
    }
}
