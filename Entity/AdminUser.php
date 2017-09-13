<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use Sylius\Component\Core\Model\AdminUser as SyliusAdminUser;
use Symfony\Component\Security\Core\User\EquatableInterface;

class AdminUser extends SyliusAdminUser implements UserInterface, EquatableInterface
{
    use User;
}
