<?php

namespace Netgen\Bundle\EzSyliusBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This listener injects a CSRF token into request in very specific case:.
 *
 * If the current request is a POST to endpoint for creating a new session
 * in eZ Publish REST API, if the session is already started and token is not
 * already present. This works around a bug in eZ Platform UI where one is unable
 * to login if session is started (e.g. by Sylius listener) before the login
 * page is shown.
 */
class PlatformUICsrfTokenListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $csrfTokenId;

    /**
     * @var \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;

    /**
     * Constructor.
     *
     * @param string $routeName
     * @param string $csrfTokenId
     * @param \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $csrfTokenManager
     */
    public function __construct(
        $routeName,
        $csrfTokenId,
        CsrfTokenManagerInterface $csrfTokenManager = null
    ) {
        $this->routeName = $routeName;
        $this->csrfTokenId = $csrfTokenId;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'onKernelRequest');
    }

    /**
     * Resolves the layout to be used for the current request.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->csrfTokenManager instanceof CsrfTokenManagerInterface) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isMethodSafe()) {
            return;
        }

        if ($request->attributes->get('_route') !== $this->routeName) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if (!$session->isStarted()) {
            return;
        }

        if ($request->headers->get('X-CSRF-Token') !== null) {
            return;
        }

        $csrfToken = $this->csrfTokenManager->getToken($this->csrfTokenId)->getValue();

        $request->headers->set('X-CSRF-Token', $csrfToken);
    }
}
