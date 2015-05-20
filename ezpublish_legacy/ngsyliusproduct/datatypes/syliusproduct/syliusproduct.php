<?php

class SyliusProduct
{
    private $price = 0;

    private $name;

    private $syliusId = null;

    private $description;

    /** @var \DateTime $availableOn */
    private $availableOn;

    private $weight = null;

    private $height = null;

    private $width = null;

    private $depth = null;

    private $sku = null;

    private $taxCategory = null;

    /*public function __construct( $price )
    {
        if ( $price < 0 )
        {
            throw new \Exception( "Price must be positive" );
        }
    }*/

    /**
     * Returns an array with attributes that are available
     *
     * @return array
     */
    public function attributes()
    {
        return array(
            'price',
            'name',
            'description',
            'sylius_id',
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
     * Initializes the product
     *
     * @param $name
     * @param $price
     * @param $description
     * @param $syliusId
     * @param $availableOn
     * @param $sku
     * @param $taxCategory
     */
    public function createFromStrings(
        $price,
        $name = null,
        $description = null,
        $syliusId = null,
        $availableOn = null,
        $sku = null,
        $taxCategory = null
    )
    {
        $this->price = $price;

        if ( $name )
        {
            $this->name = $name;
        }

        if ( $description )
        {
            $this->description = $description;
        }

        if ( $syliusId )
        {
            $this->syliusId = $syliusId;
        }

        if ( $availableOn )
        {
            $this->availableOn = $availableOn;
        }

        if ( $sku )
        {
            $this->sku = $sku;
        }

        if ( $taxCategory )
        {
            $this->taxCategory = $taxCategory;
        }
    }

    /**
     * Creates from sylius product
     *
     * @param Sylius\Component\Core\Model\Product $product
     */
    public function createFromSylius( $product )
    {
        $this->setName( $product->getName() );
        $this->setSyliusId( $product->getId() );
        $price = $product->getPrice();
        $price = $price / 100; // sylius feature
        $this->setPrice( $price );
        $this->setDescription( $product->getDescription() );

        /** @var \DateTime $availableOn */
        $availableOn = $product->getAvailableOn();
        $availableOn = $availableOn->format( 'Y-m-d H:i' );
        $this->setAvailableOn( $availableOn );

        if ( $product->getTaxCategory() )
        {
            $this->setTaxCategory( $product->getTaxCategory()->getName() );
        }

        $this->setWeight( $product->getMasterVariant()->getWeight() );
        $this->setHeight( $product->getMasterVariant()->getHeight() );
        $this->setWidth( $product->getMasterVariant()->getWidth() );
        $this->setDepth( $product->getMasterVariant()->getDepth() );
        $this->setSku( $product->getMasterVariant()->getSku() );
    }

    /**
     * Method returns string interpretation of sylius product datatype
     *
     * @return string
     */
    public function toString()
    {
        return $this->name() . '|#' .
               $this->description() . '|#' .
               $this->price() . '|#' .
               $this->availableOn() . '|#' .
               $this->weight() . '|#' .
               $this->height() . '|#' .
               $this->width() . '|#' .
               $this->depth() . '|#' .
               $this->sku() . '|#' .
               $this->taxCategory();
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
        if ( $name == 'price' )
        {
            return $this->price();
        }

        if ( $name == 'name' )
        {
            return $this->name();
        }

        if ( $name == 'sylius_id' )
        {
            return $this->syliusId();
        }

        if ( $name == 'available_on' )
        {
            return $this->availableOn();
        }

        if ( $name == 'description' )
        {
            return $this->description();
        }

        if ( $name == 'weight' )
        {
            return $this->weight();
        }

        if ( $name == 'height' )
        {
            return $this->height();
        }

        if ( $name == 'width' )
        {
            return $this->width();
        }

        if ( $name == 'depth' )
        {
            return $this->depth();
        }

        if ( $name == 'sku' )
        {
            return $this->sku();
        }

        if ( $name == 'tax_category' )
        {
            return $this->taxCategory();
        }

        eZDebug::writeError( "Attribute '$name' does not exist", "SyliusProduct::attribute" );
        return null;
    }

    /**
     * Returns price
     *
     * @return int
     */
    public function price()
    {
        return $this->price;
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name
     */
    public function setName( $name )
    {
        $this->name = $name;
    }

    /**
     * Returns sylius product id
     *
     * @return int
     */
    public function syliusId()
    {
        return $this->syliusId;
    }

    /**
     * Sets sylius id
     *
     * @param $id
     */
    public function setSyliusId( $id )
    {
        $this->syliusId = $id;
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * Sets description
     *
     * @param string $description
     */
    public function setDescription( $description )
    {
        $this->description = $description;
    }

    /**
     * Sets "available on"
     *
     * @param $availableOn
     */
    public function setAvailableOn( $availableOn )
    {
        $this->availableOn = $availableOn;
    }

    /**
     * Returns "available on" date in 'd-m-Y H:i' format
     *
     * TODO: enable different formats
     *
     * @return string
     */
    public function availableOn()
    {
        if ( $this->availableOn )
        {
            return $this->availableOn;
        }

        return null;
    }

    /**
     * Sets price
     *
     * @param $price
     */
    public function setPrice( $price )
    {
        $this->price = $price;
    }

    /**
     * Returns slug
     *
     * @return string
     */
    public function slug()
    {
        return $this->slug;
    }

    public function weight()
    {
        return $this->weight;
    }

    public function setWeight( $weight )
    {
        $this->weight = $weight;
    }

    public function height()
    {
        return $this->height;
    }

    public function setHeight( $height )
    {
        $this->height = $height;
    }

    public function width()
    {
        return $this->width;
    }

    public function setWidth( $width )
    {
        $this->width = $width;
    }

    public function depth()
    {
        return $this->depth;
    }

    public function setDepth( $depth )
    {
        $this->depth = $depth;
    }

    public function sku()
    {
        return $this->sku;
    }

    public function setSku( $sku )
    {
        $this->sku = $sku;
    }

    public function taxCategory()
    {
       return $this->taxCategory;
    }

    public function setTaxCategory( $taxCategory )
    {
        $this->taxCategory = $taxCategory;
    }
}
