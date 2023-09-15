<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context\Concern;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @extends \MyParcelNL\Pdk\Tests\Bootstrap\TestCase
 * @extends \MyParcelNL\Pdk\Tests\Integration\Context\Concern\ResolvesModels
 */
trait ValidatesValues
{
    /**
     * @return string[]
     */
    protected function getMatchers(): array
    {
        return [
            'ARRAY',
            'BOOLEAN',
            'INT',
            'FLOAT',
            'NULL',
            'LENGTH',
        ];
    }

    protected function validateByCurrentFirstOrLast(string $value, array $body, string $key): void
    {
        $type = Str::before($value, '_');
        Str::after($value, "{$type}_");

        $actualValue = Arr::get($body, $key);

        $this->validateByType($value, $key, $actualValue);
    }

    /**
     * @param         $value
     * @param         $body
     */
    protected function validateByMatcher($value, $body, string $key): void
    {
        $matchers    = explode(',', (string) $value);
        $actualValue = Arr::get($body, $key);

        foreach ($matchers as $matcher) {
            [$matchId, $matchArgs] = explode(':', strtoupper($matcher));

            switch ($matchId) {
                case 'ARRAY':
                    self::assertIsArray($actualValue, "Value for key '$key' is not an array");
                    break;

                case 'BOOLEAN':
                    self::assertIsBool($actualValue, "Value for key '$key' is not a boolean");
                    break;

                case 'INT':
                    self::assertIsInt($actualValue, "Value for key '$key' is not an integer");
                    break;

                case 'FLOAT':
                    self::assertIsFloat($actualValue, "Value for key '$key' is not a float");
                    break;

                case 'NULL':
                    self::assertNull($actualValue, "Value for key '$key' is not null");
                    break;

                case 'FILLED':
                    self::assertNotEmpty($actualValue, "Value for key '$key' is empty");
                    break;

                case 'LENGTH':
                    $this->validateLength($actualValue, (int) $matchArgs, $key);
                    break;
            }
        }
    }

    protected function validateByType(string $entityResolver, string $key, mixed $actualValue): void
    {
        $entity = $this->resolveModel($entityResolver);

        $match = preg_replace_callback_array([
            '/PLATFORM_(\w+)/' => static fn($matches) => Platform::get(strtolower((string) $matches[1])),

            // Matches models defined in ResolvesModels
            '/\w+:(\w+)/'      => fn($matches) => $this->matchModelProperty($entity, $matches[1]),
        ], $entityResolver);

        self::assertEquals((string) $match, (string) $actualValue, "Value for key '$key' does not match");
    }

    protected function validateLength(mixed $actualValue, int $length, string $key): void
    {
        if (is_array($actualValue)) {
            self::assertCount($length, $actualValue, "Value for key '$key' is not $length items long");
            return;
        }

        if (is_string($actualValue)) {
            self::assertEquals(
                strlen($actualValue),
                $length,
                "Value for key '$key' is not $length characters long"
            );

            return;
        }

        self::fail("Value for key '$key' is not a string or array");
    }

    protected function validateValue(string $key, string $value, array $body): void
    {
        if (Str::startsWith($value, ['CURRENT_', 'FIRST_', 'LAST_'])) {
            $this->validateByCurrentFirstOrLast($value, $body, $key);
            return;
        }

        if (Str::contains($value, $this->getMatchers())) {
            $this->validateByMatcher($value, $body, $key);
            return;
        }

        self::assertEquals($value, Arr::get($body, $key), "Value for key '$key' does not match");
    }
}
