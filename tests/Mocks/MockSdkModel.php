<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use ArrayAccess;
use JsonSerializable;

/**
 * A mock that mimics the structure of OpenAPI-generated SDK models.
 * Uses $container, static getters()/setters()/attributeMap() methods.
 */
class MockSdkModel implements ArrayAccess, JsonSerializable
{
    protected static $attributeMap = [
        'first_name' => 'firstName',
        'last_name'  => 'lastName',
        'age'        => 'age',
        'name'       => 'name',
    ];

    protected static $getters = [
        'first_name' => 'getFirstName',
        'last_name'  => 'getLastName',
        'age'        => 'getAge',
        'name'       => 'getName',
    ];

    protected static $setters = [
        'first_name' => 'setFirstName',
        'last_name'  => 'setLastName',
        'age'        => 'setAge',
        'name'       => 'setName',
    ];

    protected $container = [];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->container['first_name'] = $data['first_name'] ?? null;
        $this->container['last_name']  = $data['last_name'] ?? null;
        $this->container['age']        = $data['age'] ?? null;
        $this->container['name']       = $data['name'] ?? null;
    }

    public static function attributeMap(): array
    {
        return self::$attributeMap;
    }

    public static function getters(): array
    {
        return self::$getters;
    }

    public static function setters(): array
    {
        return self::$setters;
    }

    public function getFirstName(): ?string
    {
        return $this->container['first_name'] ?? null;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->container['first_name'] = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->container['last_name'] ?? null;
    }

    public function setLastName(?string $lastName): self
    {
        $this->container['last_name'] = $lastName;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->container['age'] ?? null;
    }

    public function setAge(?int $age): self
    {
        $this->container['age'] = $age;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->container['name'] ?? null;
    }

    public function setName(?string $name): self
    {
        $this->container['name'] = $name;

        return $this;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->container[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->container;
    }
}
