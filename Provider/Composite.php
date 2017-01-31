<?php

namespace Netgen\Bundle\EzSyliusBundle\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Sylius\Bundle\UserBundle\Provider\UserProviderInterface as SyliusUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Composite implements SyliusUserProviderInterface
{
    /**
     * @var \Sylius\Bundle\UserBundle\Provider\UserProviderInterface[]
     */
    protected $innerProviders = array();

    /**
     * Constructor.
     *
     * @param \Sylius\Bundle\UserBundle\Provider\UserProviderInterface[] $innerProviders
     */
    public function __construct(array $innerProviders)
    {
        $this->innerProviders = $innerProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($usernameOrEmail)
    {
        $exception = null;

        foreach ($this->innerProviders as $provider) {
            try {
                return $provider->loadUserByUsername($usernameOrEmail);
            } catch (UsernameNotFoundException $e) {
                $exception = $e;
            }
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $exception = null;

        foreach ($this->innerProviders as $provider) {
            try {
                return $provider->refreshUser($user);
            } catch (UnsupportedUserException $e) {
                $exception = $e;
            } catch (UsernameNotFoundException $e) {
                $exception = $e;
            }
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        foreach ($this->innerProviders as $provider) {
            if ($provider->supportsClass($class)) {
                return true;
            }
        }

        return false;
    }
}
