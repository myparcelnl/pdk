<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Helper\TypeScript\TypeParser;
use MyParcelNL\Pdk\Plugin\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;

it('converts to ts type', function ($input, $expected) {
    $class = new TypeParser();

    expect($class->getType($input))->toEqual($expected);
})
    ->with([
        [sprintf('\%s', ContextServiceInterface::class), 'ServiceContextServiceInterface'],
        [
            sprintf('array<string, \%s>', ContextServiceInterface::class),
            'Record<string, ServiceContextServiceInterface>',
        ],
        [sprintf('\%s', Exception::class), 'Exception'],
        [sprintf('\%s', Model::class), 'Model'],
        [sprintf('\%s', Address::class), 'ModelAddress'],
        [sprintf('\%s', DropOffDayCollection::class), 'DropOffDayCollection'],
        [sprintf('array{name: string, value: \%s}', Model::class), "{\n  name: string,\n  value: Model\n}"],
        [sprintf('array{name: string, value: \%s[]}', Model::class), "{\n  name: string,\n  value: Model[]\n}"],
        [sprintf('array{name: string, value: \%s[]}[]', Model::class), "{\n  name: string,\n  value: Model[]\n}[]"],
        [
            sprintf('array{name: string, request: array{value: \%s[]}}[]', Model::class),
            "{\n  name: string,\n  request: {\n  value: Model[]\n}\n}[]",
        ],
    ])
    ->skip();

it('converts to ts type as reference', function ($input, $expected) {
    $class = new TypeParser();

    expect($class->getType($input, true))->toEqual($expected);
})
    ->with([
        [sprintf('\%s', ContextServiceInterface::class), 'Plugin.ServiceContextServiceInterface'],
        [
            sprintf('array<string, \%s>', ContextServiceInterface::class),
            'Record<string, Plugin.ServiceContextServiceInterface>',
        ],
        [sprintf('\%s', Exception::class), 'Exception'],
        [sprintf('\%s', Model::class), 'Base.Model'],
        [sprintf('\%s', Address::class), 'Base.ModelAddress'],
        [sprintf('\%s', DropOffDayCollection::class), 'Shipment.DropOffDayCollection'],
        [sprintf('array{name: string, value: \%s}', Model::class), "{\n  name: string,\n  value: Base.Model\n}"],
        [sprintf('array{name: string, value: \%s[]}', Model::class), "{\n  name: string,\n  value: Base.Model[]\n}"],
        [
            sprintf('array{name: string, value: \%s[]}[]', Model::class),
            "{\n  name: string,\n  value: Base.Model[]\n}[]",
        ],
    ])
    ->skip();
