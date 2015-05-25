<?php

namespace Netgen\Bundle\EzSyliusBundle\Entity;

class SyliusProduct
{
    /**
     * @var int
     */
    private $contentId;

    /**
     * @var int
     */
    private $productId;


    /**
     * Get eZ Publish content ID
     *
     * @return int
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Set Sylius product ID
     *
     * @param int $productId
     *
     * @return SyliusProduct
     */
    public function setProductId( $productId )
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get Sylius product ID
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }
}

