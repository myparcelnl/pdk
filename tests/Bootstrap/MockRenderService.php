<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Service\RenderService;

class MockRenderService extends RenderService
{
    public const RENDERED_CONTENT = '(content)';

    /**
     * Skips all rendering stuff.
     *
     * @param  string $template
     * @param  array  $templateParameters
     * @param  array  $contexts
     * @param  array  $contextArguments
     *
     * @return string
     */
    protected function renderTemplate(
        string $template,
        array  $templateParameters = [],
        array  $contexts = [],
        array  $contextArguments = []
    ): string {
        return self::RENDERED_CONTENT;
    }
}
