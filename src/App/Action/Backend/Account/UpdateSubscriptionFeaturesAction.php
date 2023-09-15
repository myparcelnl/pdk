<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Repository\AclRepository;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class UpdateSubscriptionFeaturesAction implements ActionInterface
{
    public function __construct(private AclRepository                 $aclRepository,
                                private PdkAccountRepositoryInterface $pdkAccountRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        $account              = $this->pdkAccountRepository->getAccount();
        $subscriptionFeatures = $this->aclRepository->getSubscriptionFeatures();

        $account->subscriptionFeatures = $subscriptionFeatures;

        $this->pdkAccountRepository->store($account);

        return new JsonResponse([
            'subscription_features' => $subscriptionFeatures->toArray(),
        ]);
    }
}
