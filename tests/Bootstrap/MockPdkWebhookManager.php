<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Webhook\Contract\HookInterface;
use MyParcelNL\Pdk\App\Webhook\PdkWebhookManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ResetInterface;

final class MockPdkWebhookManager extends PdkWebhookManager implements ResetInterface
{
    protected $calledHooks = [];

    /**
     * @return array
     */
    public function getCalledHooks(): array
    {
        return $this->calledHooks;
    }

    public function reset(): void
    {
        $this->calledHooks = [];
    }

    protected function handleHook(HookInterface $hook, Request $request, array $logContext): void
    {
        $this->calledHooks[] = $hook;

        parent::handleHook($hook, $request, $logContext);
    }
}
