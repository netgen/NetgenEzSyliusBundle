<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

use eZ\Publish\API\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

trait User
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $apiUser;

    /**
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getAPIUser()
    {
        return $this->apiUser;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     */
    public function setAPIUser(APIUser $apiUser)
    {
        $this->apiUser = $apiUser;
    }

    /**
     * Compares the users.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(BaseUserInterface $user)
    {
        if ($user instanceof self && $this->apiUser instanceof APIUser) {
            return $user->getAPIUser()->id === $this->apiUser->id;
        }

        return false;
    }

    /**
     * Returns string representation of the user.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->apiUser->contentInfo->name;
    }
}
