<?php

namespace Netgen\Bundle\EzSyliusBundle\Provider;

use eZ\Publish\API\Repository\Values\User\UserReference;
use Sylius\Bundle\UserBundle\Provider\UserProviderInterface as SyliusUserProviderInterface;

interface UserProviderInterface extends SyliusUserProviderInterface
{
    /**
     * Loads Sylius user based on provided eZ API user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $apiUser
     *
     * @return \Sylius\Component\User\Model\UserInterface
     */
    public function loadUserByAPIUser(UserReference $apiUser);
}
