<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser;
use Symfony\Component\PropertyInfo\Type;

/**
 * @property Type[] $items
 */
class TypeCollection extends Collection
{
    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser
     */
    private $typeParser;

    /**
     * @param  array $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
        $this->typeParser = new PhpTypeParser();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this
            ->getTypeStrings()
            ->implode('|');
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return $this
            ->getTypeStrings()
            ->all();
    }

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return $this
            ->map(function (Type $type) {
                return $this->typeParser->getTypeAsString($type);
            })
            ->toArray();
    }

    /**
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection
     */
    protected function getTypeStrings(): TypeCollection
    {
        return $this->map(function (Type $type) {
            return $this->typeParser->getTypeAsString($type);
        });
    }
}
