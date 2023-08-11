<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use BadMethodCallException;
use InvalidArgumentException;

it('throws error when getting a non-existing property', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    /** @noinspection PhpUndefinedFieldInspection */
    $formCondition->propertyThatDoesNotExist;
})->throws(InvalidArgumentException::class);

it('throws error when setting a property', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    /** @noinspection PhpUndefinedFieldInspection */
    $formCondition->property = 'foo';
})->throws(BadMethodCallException::class);

it('throws error when checking if a property is set', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    /** @noinspection PhpUndefinedFieldInspection */
    /** @noinspection PhpExpressionResultUnusedInspection */
    isset($formCondition->property);
})->throws(BadMethodCallException::class);

it('throws error when unsetting a property', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    unset($formCondition->property);
})->throws(BadMethodCallException::class);
