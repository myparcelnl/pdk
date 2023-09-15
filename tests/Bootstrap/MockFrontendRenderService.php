<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;

class MockFrontendRenderService extends FrontendRenderService
{
    final public const RENDERED_CONTENT = '(content)';

    public function renderSomething(string $component): string
    {
        if (! $this->shouldRender($component)) {
            return '';
        }

        return self::RENDERED_CONTENT;
    }

    /**
     * Skips all rendering stuff.
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
