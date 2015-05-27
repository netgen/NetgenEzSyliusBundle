<?php

namespace Netgen\Bundle\EzSyliusBundle\Context;

use Sylius\Bundle\CoreBundle\Context\CurrencyContext as BaseCurrencyContext;
use Doctrine\DBAL\Exception\TableNotFoundException;

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
}
