<?php

namespace Netgen\Bundle\EzSyliusBundle\Authentication;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider as BaseAuthenticationProvider;

class DaoAuthenticationProvider extends BaseAuthenticationProvider
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        parent::checkAuthentication($user, $token);

        if ($user instanceof EzUserInterface) {
            $apiUser = $user->getAPIUser();

            if ($apiUser instanceof User) {
                $this->repository->setCurrentUser($apiUser);
            }
        }
    }
}
