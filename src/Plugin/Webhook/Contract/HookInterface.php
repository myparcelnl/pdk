<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Webhook\Contract;

use Symfony\Component\HttpFoundation\Request;

interface HookInterface
{
    /**
     * Process the request.
     */
    public function handle(Request $request): void;

    /**
     * Check if the request should be processed.
     */
    public function validate(Request $request): bool;
}
