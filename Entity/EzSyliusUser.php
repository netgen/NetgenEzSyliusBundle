<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

class EzSyliusUser
{
    /**
     * @var int
     */
    protected $syliusUserId;

    /**
     * @var string
     */
    protected $syliusUserType;

    /**
     * @var int
     */
    protected $eZUserId;

    /**
     * Set Sylius user ID.
     *
     * @param int $syliusUserId
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Entity\EzSyliusUser
     */
    public function setSyliusUserId($syliusUserId)
    {
        $this->syliusUserId = $syliusUserId;

        return $this;
    }

    /**
     * Get Sylius user ID.
     *
     * @return int
     */
    public function getSyliusUserId()
    {
        return $this->syliusUserId;
    }

    /**
     * Set Sylius user type.
     *
     * @param int $syliusUserType
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Entity\EzSyliusUser
     */
    public function setSyliusUserType($syliusUserType)
    {
        $this->syliusUserType = $syliusUserType;

        return $this;
    }

    /**
     * Get Sylius user type.
     *
     * @return string
     */
    public function getSyliusUserType()
    {
        return $this->syliusUserType;
    }

    /**
     * Set eZ Publish user ID.
     *
     * @param int $eZUserId
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Entity\EzSyliusUser
     */
    public function setEzUserId($eZUserId)
    {
        $this->eZUserId = $eZUserId;

        return $this;
    }

    /**
     * Get eZ Publish user ID.
     *
     * @return int
     */
    public function getEzUserId()
    {
        return $this->eZUserId;
    }
}
