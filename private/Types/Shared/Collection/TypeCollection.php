<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser;
use Stringable;
use Symfony\Component\PropertyInfo\Type;

/**
 * @property Type[] $items
 */
class TypeCollection extends Collection implements Stringable
{
    private readonly PhpTypeParser $typeParser;

    public function __construct(array $items = [])
    {
        parent::__construct($items);
        $this->typeParser = new PhpTypeParser();
    }

    public function __toString(): string
    {
        return $this->getTypeStrings()
            ->implode('|');
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->getTypeStrings()
            ->all();
    }

    public function toStorableArray(): array
    {
        return array_map(fn(Type $type) => $this->typeParser->getTypeAsString($type), $this->items);
    }

    protected function getTypeStrings(): TypeCollection
    {
        return $this->map(fn(Type $type) => $this->typeParser->getTypeAsString($type));
    }
}
