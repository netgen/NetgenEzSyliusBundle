<?php

use Sylius\Component\Core\Model\ProductInterface;

class SyliusProduct
{
    /**
     * @var \Sylius\Component\Core\Model\Product
     */
    protected $product;

    /**
     * Returns an array with attributes that are available
     *
     * @return array
     */
    public function attributes()
    {
        return array(
            'product_id',
            'price',
            'name',
            'description',
            'available_on',
            'weight',
            'height',
            'width',
            'depth',
            'sku',
            'tax_category'
        );
    }

    /**
     * Creates from Sylius product
     *
     * @param Sylius\Component\Core\Model\Product $product
     */
    public function createFromSylius( $product )
    {
        $this->product = $product;
    }

    /**
     * Method returns string interpretation of sylius product datatype
     *
     * @return string
     */
    public function toString()
    {
        return $this->product->getName() . '|#' .
               $this->product->getDescription() . '|#' .
               $this->product->getPrice() . '|#' .
               $this->product->getAvailableOn()->format( 'd-m-Y' ) . '|#' .
               $this->product->getMasterVariant()->getWeight(). '|#' .
               $this->product->getMasterVariant()->getHeight() . '|#' .
               $this->product->getMasterVariant()->getWidth() . '|#' .
               $this->product->getMasterVariant()->getDepth() . '|#' .
               $this->product->getMasterVariant()->getSku() . '|#' .
               $this->product->getTaxCategory()->getName();
    }

    /**
     * Returns true if the provided attribute exists
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

    /**
     * Returns the specified attribute
     *
     * @param string $name
     * @return mixed
     */
    public function attribute( $name )
    {
        if ( empty( $this->product ) )
        {
            return null;
        }

        if ( $name == 'product_id' )
        {
            return $this->product->getId();
        }

        if ( $name == 'price' )
        {
            return $this->product->getPrice() / 100;
        }

        if ( $name == 'name' )
        {
            return $this->product->getName();
        }

        if ( $name == 'available_on' )
        {
            return $this->product->getAvailableOn()->getTimestamp();
        }

        if ( $name == 'description' )
        {
            return $this->product->getDescription();
        }

        if ( $name == 'weight' )
        {
            return $this->product->getMasterVariant()->getWeight();
        }

        if ( $name == 'height' )
        {
            return $this->product->getMasterVariant()->getHeight();
        }

        if ( $name == 'width' )
        {
            return $this->product->getMasterVariant()->getWidth();
        }

        if ( $name == 'depth' )
        {
            return $this->product->getMasterVariant()->getDepth();
        }

        if ( $name == 'sku' )
        {
            return $this->product->getMasterVariant()->getSku();
        }

        if ( $name == 'tax_category' )
        {
            return $this->product->getTaxCategory();
        }

        eZDebug::writeError( "Attribute '$name' does not exist", "SyliusProduct::attribute" );
        return null;
    }

    /**
     * Returns embedded Sylius product
     *
     * @return \Sylius\Component\Core\Model\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets the embedded Sylius product
     *
     * @param \Sylius\Component\Core\Model\ProductInterface $product
     */
    public function setProduct( ProductInterface $product )
    {
        $this->product = $product;
    }

    /**
     * Stores the attribute
     *
     * @param eZContentObjectAttribute $attribute
     */
    public function store( eZContentObjectAttribute $attribute )
    {
        if ( !is_numeric( $attribute->attribute( 'contentobject_id' ) ) || !is_numeric( $this->product->getId() ) )
        {
            return;
        }

        $contentId = $attribute->attribute( 'contentobject_id' );
        $productId = $this->attribute( 'product_id' );

        $db = eZDB::instance();

        $result = $db->arrayQuery( "SELECT COUNT(*) as count FROM ngsyliusproduct WHERE contentobject_id = " . (int)$contentId );
        $count = $result[0]['count'];

        if ( $count > 0 )
        {
            $db->query(
                "UPDATE ngsyliusproduct SET product_id = " . (int)$productId .
                " WHERE contentobject_id = " . (int)$contentId
            );
        }
        else
        {
            $db->query(
                "INSERT INTO ngsyliusproduct ( contentobject_id, product_id )" .
                " VALUES ( " . (int)$contentId . ", " . (int)$productId . " )"
            );
        }
    }
}
