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
     * Constructor.
     *
     * @param int $syliusUserId
     * @param string $syliusUserType
     */
    public function __construct($syliusUserId, $syliusUserType)
    {
        $this->syliusUserId = $syliusUserId;
        $this->syliusUserType = $syliusUserType;
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
