<?php

namespace Netgen\Bundle\EzSyliusBundle\Security\EventListener;

use eZ\Publish\Core\MVC\Symfony\Security\EventListener\SecurityListener as BaseSecurityListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent as BaseInteractiveLoginEvent;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Sylius\Component\Core\Model\User as SyliusUser;

class SecurityListener extends BaseSecurityListener
{
    /**
     * Returns either original Sylius user or passes execution to eZ Publish
     * if no Sylius user is detected
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $originalUser
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Security\UserInterface
     */
    protected function getUser( UserInterface $originalUser, APIUser $apiUser )
    {
        if ( $originalUser instanceof SyliusUser )
        {
            return $originalUser;
        }

        return parent::getUser( $originalUser, $apiUser );
    }

    /**
     * Skips interactive login if the user comes from Sylius
     *
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onInteractiveLogin( BaseInteractiveLoginEvent $event )
    {
        if ( $event->getAuthenticationToken()->getUser() instanceof SyliusUser )
        {
            return;
        }

        parent::onInteractiveLogin( $event );
    }
}
