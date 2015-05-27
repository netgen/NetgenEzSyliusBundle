<?php

namespace Netgen\Bundle\EzSyliusBundle\Context;

use Sylius\Bundle\CoreBundle\Context\CurrencyContext as BaseCurrencyContext;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Sylius\Component\Core\Model\UserInterface;

class CurrencyContext extends BaseCurrencyContext
{
    public function getDefaultCurrency()
    {
        try
        {
            return parent::getDefaultCurrency();
        }
        catch ( TableNotFoundException $e )
        {
            return null;
        }
    }

    protected function getUser()
    {
        if (
            $this->securityContext->getToken() &&
            $this->securityContext->getToken()->getUser() instanceof UserInterface
        )
        {
            return parent::getUser();
        }

        return null;
    }
}
