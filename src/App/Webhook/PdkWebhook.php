<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Api\Contract\PdkApiInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PdkWebhook implements PdkApiInterface
{
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
     * @param  mixed  $input
     * @param  string $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call($input, string $context = 'webhook'): Response
    {
        if (! $input instanceof Request) {
            throw new InvalidArgumentException('Input must be an instance of ' . Request::class);
        }

        $this->cronService->dispatch([$this, 'processWebhook'], $input);

        return new JsonResponse(null, 202);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function processWebhook(Request $request): void
    {
        $requiredPath = parse_url($this->webhooksRepository->getHashedUrl(), PHP_URL_PATH);
        $logContext   = ['request' => get_object_vars($request)];

        if ($request->getRequestUri() !== $requiredPath) {
            Logger::error('Webhook received with invalid url', $logContext);
            return;
        }

        Logger::debug('Webhook received', $logContext);

        foreach ($this->getHooks($request) as $hook) {
            try {
                $hook = $this->resolveHook($hook['event'] ?? null);

                if (! $hook->validate($request)) {
                    Logger::debug('Webhook skipped', $logContext);
                    continue;
                }

                $hook->handle($request);

                Logger::debug('Webhook processed', $logContext);
            } catch (Throwable $exception) {
                Logger::error('Webhook failed', $logContext);
            }
        }
    }

    /**
     * @param  string $event
     *
     * @return mixed
     */
    public function resolveHook(string $event)
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
        $body = json_decode($request->getContent(), true);
        return $body['data']['hooks'] ?? [];
    }
}