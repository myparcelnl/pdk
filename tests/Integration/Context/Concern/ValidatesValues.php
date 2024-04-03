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

    /**
     * @param  string $value
     * @param  array  $body
     * @param  string $key
     *
     * @return void
     */
    protected function validateByCurrentFirstOrLast(string $value, array $body, string $key): void
    {
        $type   = Str::before($value, '_');
        $entity = Str::after($value, "{$type}_");

        $actualValue = Arr::get($body, $key);

        $this->validateByType($value, $key, $actualValue);
    }

    /**
     * @param         $value
     * @param         $body
     * @param  string $key
     *
     * @return void
     */
    protected function validateByMatcher($value, $body, string $key): void
    {
        $matchers    = explode(',', $value);
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

                case 'STRING':
                    self::assertIsString($actualValue, "Value for key '$key' is not a string");
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

    /**
     * @param  string $entityResolver
     * @param  string $key
     * @param  mixed  $actualValue
     *
     * @return void
     */
    protected function validateByType(string $entityResolver, string $key, $actualValue): void
    {
        $entity = $this->resolveModel($entityResolver);

        $match = preg_replace_callback_array([
            '/PLATFORM_(\w+)/' => static function ($matches) {
                return Platform::get(strtolower($matches[1]));
            },

            // Matches models defined in ResolvesModels
            '/\w+:(\w+)/'      => function ($matches) use ($entity) {
                return $this->matchModelProperty($entity, $matches[1]);
            },
        ], $entityResolver);

        self::assertEquals((string) $match, (string) $actualValue, "Value for key '$key' does not match");
    }

    /**
     * @param  mixed  $actualValue
     * @param  int    $length
     * @param  string $key
     *
     * @return void
     */
    protected function validateLength($actualValue, int $length, string $key): void
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

    /**
     * @param  string $key
     * @param  string $value
     * @param  array  $body
     *
     * @return void
     */
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
