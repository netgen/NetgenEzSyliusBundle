<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

abstract class EzSyliusUser
{
    /**
     * @var int
     */
    protected $eZUserId;

    /**
     * @var int
     */
    protected $syliusUserId;

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
}
