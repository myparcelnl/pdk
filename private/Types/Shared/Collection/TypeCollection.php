<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser;
use Symfony\Component\PropertyInfo\Type;

/**
 * @property Type $items
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
     * @return string[]
     */
    public function getNames(): array
    {
        return $this
            ->map(function (Type $type) {
                return $this->typeParser->getTypeAsString($type);
            })
            ->all();
    }
}
