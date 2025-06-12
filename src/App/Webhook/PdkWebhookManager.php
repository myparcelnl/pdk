<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Webhook\Contract\HookInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PdkWebhookManager implements PdkWebhookManagerInterface
{
    private const CONTEXT_WEBHOOK = 'webhook';

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CronServiceInterface
     */
    protected $cronService;

    /**
     * @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface
     */
    protected $webhooksRepository;

    /**
     * @param  \MyParcelNL\Pdk\Base\Contract\CronServiceInterface                  $cronService
     * @param  \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $webhooksRepository
     */
    public function __construct(CronServiceInterface $cronService, PdkWebhooksRepositoryInterface $webhooksRepository)
    {
        $this->cronService        = $cronService;
        $this->webhooksRepository = $webhooksRepository;
    }

    /**
     * @param  Request $input
     * @param  string  $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call($input, string $context = self::CONTEXT_WEBHOOK): Response
    {
        $response = new Response(null, Response::HTTP_ACCEPTED);

        if (self::CONTEXT_WEBHOOK !== $context || ! $input instanceof Request) {
            Logger::error('Webhook called with invalid input', compact('input', 'context'));

            return $response;
        }

        $this->cronService->dispatch([$this, 'processWebhook'], $input);

        return $response;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function processWebhook(Request $request): void
    {
        $hashedUrl    = $this->webhooksRepository->getHashedUrl();
        $requiredPath = parse_url($hashedUrl, PHP_URL_PATH);
        if ($query = parse_url($hashedUrl, PHP_URL_QUERY)) {
            $requiredPath .= '?' . $query;
        }
        $logContext = ['request' => get_object_vars($request)];

        if ($request->getRequestUri() !== $requiredPath) {
            Logger::error('Webhook received with invalid url', $logContext);

            return;
        }

        Logger::debug('Webhook received', $logContext);

        foreach ($this->getHooks($request) as $hook) {
            try {
                $hook               = $this->resolveHook($hook['event'] ?? null);
                $logContext['hook'] = get_class($hook);

                if (! $hook->validate($request)) {
                    Logger::debug('Webhook skipped', $logContext);

                    continue;
                }

                $this->handleHook($hook, $request, $logContext);
            } catch (Throwable $exception) {
                Logger::error('Webhook failed', $logContext);
            }
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Webhook\Contract\HookInterface $hook
     * @param  \Symfony\Component\HttpFoundation\Request          $request
     * @param  array                                              $logContext
     *
     * @return void
     */
    protected function handleHook(HookInterface $hook, Request $request, array $logContext): void
    {
        $hook->handle($request);
        Logger::debug('Webhook processed', $logContext);
    }

    /**
     * @param  string $event
     *
     * @return \MyParcelNL\Pdk\App\Webhook\Contract\HookInterface
     */
    protected function resolveHook(string $event): HookInterface
    {
        $supportedWebhooks = Config::get('webhooks');
        $hookClass         = Arr::get($supportedWebhooks, $event);

        if (! $hookClass) {
            throw new InvalidArgumentException("Unsupported webhook event: $event");
        }

        return Pdk::get($hookClass);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    private function getHooks(Request $request): array
    {
        $myParcelHeader = $request->headers->get('x-myparcel-hook');
        $body           = json_decode($request->getContent(), true);
        $hooks          = $body['data']['hooks'] ?? [];

        return array_map(static function ($hook) use ($myParcelHeader) {
            $hook['event'] = $hook['event'] ?? $myParcelHeader;

            return $hook;
        }, $hooks);
    }
}
