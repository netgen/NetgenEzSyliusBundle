<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use Sylius\Component\Core\Model\ShopUser as SyliusShopUser;
use Symfony\Component\Security\Core\User\EquatableInterface;

class ShopUser extends SyliusShopUser implements UserInterface, EquatableInterface
{
    use User;
}
