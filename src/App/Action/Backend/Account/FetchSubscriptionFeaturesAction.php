<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository;
use MyParcelNL\Pdk\Account\Repository\SubscriptionFeaturesRepository;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchSubscriptionFeaturesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\SubscriptionFeaturesRepository
     */
    private $subscriptionFeaturesRepository;

    /**
     * @param  \MyParcelNL\Pdk\Account\Repository\SubscriptionFeaturesRepository $subscriptionFeaturesRepository
     */
    public function __construct(
        SubscriptionFeaturesRepository $subscriptionFeaturesRepository
    ) {
        $this->subscriptionFeaturesRepository = $subscriptionFeaturesRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        return new JsonResponse($this->subscriptionFeaturesRepository->getSubscriptionFeatures());
    }
}
