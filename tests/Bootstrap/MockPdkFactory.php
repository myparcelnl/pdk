<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;

final class MockPdkFactory extends PdkFactory
{
    /**
     * @param ...$config
     *
     * @return \MyParcelNL\Pdk\Base\Concern\PdkInterface
     * @throws \Exception
     */
    public static function create(...$config): PdkInterface
    {
        return parent::create(MockPdkConfig::create(...$config));
    }
}
