<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Repository\AclRepository;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchSubscriptionFeaturesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AclRepository
     */
    private $subscriptionFeaturesRepository;

    /**
     * @param  \MyParcelNL\Pdk\Account\Repository\AclRepository $subscriptionFeaturesRepository
     */
    public function __construct(AclRepository $subscriptionFeaturesRepository)
    {
        $this->subscriptionFeaturesRepository = $subscriptionFeaturesRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        return new JsonResponse([
            'subscription_features' => $this->subscriptionFeaturesRepository->getSubscriptionFeatures(),
        ]);
    }
}
