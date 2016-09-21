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
    protected $productId;

    /**
     * Constructor.
     *
     * @param int $contentId
     * @param int $productId
     */
    public function __construct($contentId, $productId)
    {
        $this->contentId = $contentId;
        $this->productId = $productId;
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
}
