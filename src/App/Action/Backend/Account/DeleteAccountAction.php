<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteAccountAction extends UpdateAccountAction
{
    public function handle(Request $request): Response
    {
        $this->updateAccountSettings([]);

        return Actions::execute(PdkBackendActions::UPDATE_ACCOUNT);
    }
}
