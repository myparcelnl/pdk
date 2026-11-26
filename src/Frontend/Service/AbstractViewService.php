<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;

abstract class AbstractViewService implements ViewServiceInterface
{
    /**
     * @return bool
     */
    public function hasModals(): bool
    {
        return $this->isOrderListPage() || $this->isOrderPage();
    }

    /**
     * @return bool
     */
    public function hasNotifications(): bool
    {
        // The plugins overview only shows the deactivation modal.
        return $this->isAnyPdkPage() && ! $this->isPluginsOverviewPage();
    }

    /**
     * @return bool
     */
    public function isAnyPdkPage(): bool
    {
        return $this->isChildProductPage()
            || $this->isOrderListPage()
            || $this->isOrderPage()
            || $this->isPluginSettingsPage()
            || $this->isPluginsOverviewPage()
            || $this->isProductPage();
    }

    /**
     * Plugins that do not implement this do not show the deactivation modal.
     *
     * @return bool
     */
    public function isPluginsOverviewPage(): bool
    {
        return false;
    }
}
