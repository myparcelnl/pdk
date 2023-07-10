<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Contract;

use MyParcelNL\Pdk\App\Api\Contract\PdkApiInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface PdkWebhookManagerInterface extends PdkApiInterface
{
    /**
     * Schedules a webhook call using the cron service. Context is automatically set to 'webhook', and should not be
     * changed.
     * Must always return a response with status code 202 (accepted), as soon as possible.
     *
     * @param  Request $input
     *
     * @see \MyParcelNL\Pdk\Context\Contract\ContextServiceInterface
     */
    public function call($input, string $context = 'webhook'): Response;

    /**
     * The callback that finds and executes the requested webhook's logic.
     */
    public function processWebhook(Request $request): void;
}
