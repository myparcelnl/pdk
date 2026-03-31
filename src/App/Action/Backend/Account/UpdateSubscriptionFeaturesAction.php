<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\SdkApi\Service\Iam\WhoamiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fetches the current account features from the IAM /whoami endpoint and
 * stores them on the account model for later use by PdkAccountFeaturesService.
 *
 * Replaces the former AclRepository-based implementation. The action name is
 * kept for backward compatibility with plugin integrations.
 *
 * @see \MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService
 * @see \MyParcelNL\Pdk\SdkApi\Service\Iam\WhoamiService
 */
final class UpdateSubscriptionFeaturesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    private $pdkAccountRepository;

    /**
     * @var \MyParcelNL\Pdk\SdkApi\Service\Iam\WhoamiService
     */
    private $whoamiService;

    /**
     * @param  \MyParcelNL\Pdk\SdkApi\Service\Iam\WhoamiService                   $whoamiService
     * @param  \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $pdkAccountRepository
     */
    public function __construct(WhoamiService $whoamiService, PdkAccountRepositoryInterface $pdkAccountRepository)
    {
        $this->whoamiService        = $whoamiService;
        $this->pdkAccountRepository = $pdkAccountRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $account = $this->pdkAccountRepository->getAccount();
        $whoami  = $this->whoamiService->getWhoami();

        $features = new Collection($whoami->getFeatures() ?? []);

        $account->subscriptionFeatures = $features;

        $this->pdkAccountRepository->store($account);

        return new JsonResponse([
            'subscription_features' => $features->toArray(),
        ]);
    }
}
