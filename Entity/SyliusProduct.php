<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

class SyliusProduct
{
    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var int
     */
    protected $productId;

    /**
     * Constructor.
     *
     * @param int $contentId
     * @param int $productId
     * @param int $version
     */
    public function __construct($contentId, $productId, $version)
    {
        $this->contentId = $contentId;
        $this->productId = $productId;
        $this->version = $version;
    }

    /**
     * Get eZ Publish content ID.
     *
     * @return int
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Get Sylius product ID.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
