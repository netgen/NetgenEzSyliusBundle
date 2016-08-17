<?php

namespace Netgen\Bundle\EzSyliusBundle\Provider;

use Netgen\Bundle\EzSyliusBundle\Entity\EzSyliusUser;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\MVC\Symfony\Security\User\APIUserProviderInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;

class UserProvider implements APIUserProviderInterface
{
    /**
     * @var \Sylius\Component\User\Repository\UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $entityRepository;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $supportedClass;

    /**
     * Constructor.
     *
     * @param \Sylius\Component\User\Repository\UserRepositoryInterface $userRepository
     * @param \Doctrine\ORM\EntityRepository $entityRepository
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param string $supportedClass
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        EntityRepository $entityRepository,
        Repository $repository,
        $supportedClass
    ) {
        $this->userRepository = $userRepository;
        $this->entityRepository = $entityRepository;
        $this->repository = $repository;
        $this->supportedClass = $supportedClass;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException If the user is not found
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function loadUserByUsername($username)
    {
        try {
            $apiUser = $this->repository->getUserService()->loadUserByLogin($username);
        } catch (NotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage(), 0, $e);
        }

        return $this->loadUserByAPIUser($apiUser);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException If the user is not found
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException If the account is not supported
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $reloadedUser = $this->userRepository->find($user->getId());

        if ($reloadedUser === null) {
            throw new UsernameNotFoundException(
                sprintf('User with ID "%d" could not be refreshed.', $user->getId())
            );
        }

        $eZSyliusUser = $this->entityRepository->findOneBy(array('syliusUserId' => $user->getId()));

        if (!$eZSyliusUser instanceof EzSyliusUser) {
            throw new UsernameNotFoundException(
                sprintf('User with ID "%d" could not be refreshed.', $user->getId())
            );
        }

        try {
            $apiUser = $this->repository->getUserService()->loadUser($eZSyliusUser->getEzUserId());
        } catch (NotFoundException $e) {
            throw new UsernameNotFoundException(
                sprintf('User with ID "%d" could not be refreshed.', $user->getId())
            );
        }

        if ($reloadedUser instanceof UserInterface) {
            $reloadedUser->setAPIUser($apiUser);
        }

        $this->repository->setCurrentUser($apiUser);

        return $reloadedUser;
    }

    /**
     * Loads a regular user object, usable by Symfony Security component, from a user object returned by Public API.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException If the user is not found
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Security\UserInterface
     */
    public function loadUserByAPIUser(APIUser $apiUser)
    {
        $eZSyliusUser = $this->entityRepository->find($apiUser->id);

        if (!$eZSyliusUser instanceof EzSyliusUser) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $apiUser->login)
            );
        }

        $syliusUser = $this->userRepository->find($eZSyliusUser->getSyliusUserId());
        if ($syliusUser === null) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $apiUser->login)
            );
        }

        if ($syliusUser instanceof UserInterface) {
            $syliusUser->setAPIUser($apiUser);
        }

        return $syliusUser;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === $this->supportedClass || is_subclass_of($class, $this->supportedClass);
    }
}
