<?php

namespace Netgen\Bundle\EzSyliusBundle\EventListener;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Legacy\Security\LegacyToken;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Netgen\Bundle\EzSyliusBundle\Provider\UserProviderInterface;

class LegacyRequestListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Netgen\Bundle\EzSyliusBundle\Provider\UserProviderInterface
     */
    protected $userProvider;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Netgen\Bundle\EzSyliusBundle\Provider\UserProviderInterface $userProvider
     */
    public function __construct(
        ConfigResolverInterface $configResolver,
        Repository $repository,
        TokenStorageInterface $tokenStorage,
        UserProviderInterface $userProvider
    ) {
        $this->configResolver = $configResolver;
        $this->repository = $repository;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }

    /**
     * If user is logged-in in legacy_mode (e.g. legacy admin interface),
     * will inject currently logged-in user in the repository.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver */
        $request = $event->getRequest();
        $session = $request->getSession();
        if (
            $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST
            || !$this->configResolver->hasParameter('legacy_mode')
            || !$this->configResolver->getParameter('legacy_mode')
            || !($session->isStarted() && $session->has('eZUserLoggedInID'))
        ) {
            return;
        }

        try {
            $apiUser = $this->repository->getUserService()->loadUser($session->get('eZUserLoggedInID'));
            $this->repository->setCurrentUser($apiUser);

            $token = $this->tokenStorage->getToken();
            if ($token instanceof TokenInterface) {
                $user = $this->userProvider->loadUserByAPIUser($apiUser);
                if (!$user instanceof UserInterface) {
                    $this->cleanup($session);

                    return;
                }

                $token->setUser($user);
                // Don't embed if we already have a LegacyToken, to avoid nested session storage.
                if (!$token instanceof LegacyToken) {
                    $this->tokenStorage->setToken(new LegacyToken($token));
                }
            }
        } catch (NotFoundException $e) {
            $this->cleanup($session);
        } catch (UsernameNotFoundException $e) {
            $this->cleanup($session);
        }
    }

    /**
     * Called on invalid user ID, when the user may have been removed.
     *
     * Invalidates the token and the session.
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    protected function cleanup(SessionInterface $session)
    {
        $this->tokenStorage->setToken(null);
        $session->invalidate();
    }
}
