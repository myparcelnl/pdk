<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Sdk\Client\Generated\IamApi\Model\Feature;

/**
 * PDK feature service — maps IAM whoami features to business capabilities.
 *
 * This service reads the stored whoami features from the Account model and applies
 * PDK-specific business rules on top of them, such as:
 * - Order v2 takes precedence over Order v1 when both are present
 * - Fallback to shipments mode (0) when neither v1 nor v2 is present
 *
 * Feature keys are sourced from the generated IAM SDK model where possible.
 *
 * The underlying features are populated by {@see \MyParcelNL\Pdk\App\Action\Backend\Account\UpdateSubscriptionFeaturesAction},
 * which fetches them from the IAM /whoami endpoint via {@see \MyParcelNL\Pdk\SdkApi\Service\Iam\WhoamiService}.
 */
class PdkAccountFeaturesService implements AccountFeaturesServiceInterface
{
    /**
     * IAM feature key: order notes (create/edit/delete order notes).
     */
    public const FEATURE_ORDER_NOTES = Feature::ORDER_NOTES;

    /**
     * IAM feature key: direct printing (print labels without download step).
     */
    public const FEATURE_DIRECT_PRINTING = Feature::DIRECT_PRINTING;

    /**
     * IAM feature key: My Returns portal access.
     * Not yet present in the IAM API; defaults to false until the API surfaces it.
     */
    public const FEATURE_MY_RETURNS = 'MY_RETURNS';

    /**
     * IAM feature key: Order management v2 (Vasco/Order v2 platform).
     * When present, Order v2 behaviour applies. Wins over LEGACY_ORDER_MANAGEMENT.
     */
    public const FEATURE_ORDER_MANAGEMENT = Feature::ORDER_MANAGEMENT;

    /**
     * IAM feature key: Order management v1 (legacy order mode).
     */
    public const FEATURE_LEGACY_ORDER_MANAGEMENT = Feature::LEGACY_ORDER_MANAGEMENT;

    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $accountRepository
     */
    public function __construct(PdkAccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @return bool
     */
    public function canUseOrderNotes(): bool
    {
        return $this->hasFeature(self::FEATURE_ORDER_NOTES);
    }

    /**
     * @return bool
     */
    public function canUseDirectPrinting(): bool
    {
        return $this->hasFeature(self::FEATURE_DIRECT_PRINTING);
    }

    /**
     * @return bool
     */
    public function canUseMyReturns(): bool
    {
        return $this->hasFeature(self::FEATURE_MY_RETURNS);
    }

    /**
     * @return bool
     */
    public function usesOrderMode(): bool
    {
        return $this->getOrderModeVersion() > 0;
    }

    /**
     * Returns:
     *   ORDER_MODE_V2 — ORDER_MANAGEMENT (v2) takes precedence when both v1 and v2 are present
     *   ORDER_MODE_V1 — LEGACY_ORDER_MANAGEMENT (v1) only
     *   ORDER_MODE_SHIPMENTS — neither present; shop uses shipments (fallback)
     *
     * @return int
     */
    public function getOrderModeVersion(): int
    {
        if ($this->hasFeature(self::FEATURE_ORDER_MANAGEMENT)) {
            return self::ORDER_MODE_V2;
        }

        if ($this->hasFeature(self::FEATURE_LEGACY_ORDER_MANAGEMENT)) {
            return self::ORDER_MODE_V1;
        }

        return self::ORDER_MODE_SHIPMENTS;
    }

    /**
     * The order management version the PDK should behave as.
     *
     * Use this from any code path that adapts behaviour to the order management mode —
     * export dispatch, bulk actions, settings filtering, UI gating, webhook routing.
     * Keep {@see self::getOrderModeVersion()} for code that genuinely needs the raw IAM
     * feature value (display, diagnostics).
     *
     * In this phase the value is identical to the raw mode; the hybrid-V2 behaviour
     * lives in the callers rather than here.
     *
     * @TODO INT-1590: downgrade ORDER_MODE_V2 to ORDER_MODE_SHIPMENTS when the
     *       customer has no active sales channel, so manual shipment export keeps
     *       working without callers needing per-mode branches.
     *
     * @return int
     */
    public function getEffectiveOrderMode(): int
    {
        return $this->getOrderModeVersion();
    }

    /**
     * @param  string $featureName
     *
     * @return bool
     */
    private function hasFeature(string $featureName): bool
    {
        $account = $this->accountRepository->getAccount();

        if (null === $account) {
            return false;
        }

        return in_array($featureName, $account->subscriptionFeatures->toArray(), true);
    }
}
