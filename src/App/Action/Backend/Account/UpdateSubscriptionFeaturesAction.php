<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Repository\AclRepository;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateSubscriptionFeaturesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AclRepository
     */
    private $aclRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    private $pdkAccountRepository;

    /**
     * @param  \MyParcelNL\Pdk\Account\Repository\AclRepository                   $aclRepository
     * @param  \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $pdkAccountRepository
     */
    public function __construct(AclRepository $aclRepository, PdkAccountRepositoryInterface $pdkAccountRepository)
    {
        $this->aclRepository        = $aclRepository;
        $this->pdkAccountRepository = $pdkAccountRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
