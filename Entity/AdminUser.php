<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

use Sylius\Component\Core\Model\AdminUser as SyliusAdminUser;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class AdminUser extends SyliusAdminUser implements UserInterface, EquatableInterface
{
    use User;
}
