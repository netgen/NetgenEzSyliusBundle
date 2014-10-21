<?php

/**
 * SyliusProduct class implements functions used by syliusproduct datatype
 *
 */
class SyliusProduct
{
    private $price = 0;

    private $name;

    private $sylius_id = null;

    private $description;

    /** @var \DateTime $available_on */
    private $available_on;

    private $weight = null;

    private $height = null;

    private $width = null;

    private $sku = null;

    private $tax_category = null;


    /*public function __construct( $price )
    {
        if ($price < 0)
        {
            throw new \Exception("Price must be positive");
        }
    }*/

    /**
     * Returns an array with attributes that are available
     *
     * @return array
     */
    function attributes()
    {
        return array( 'price',
                      'name',
                      'description',
                      'sylius_id',
                      'available_on',
                      'weight',
                      'height',
                      'width',
                      'sku',
                      'tax_category');
    }

    /**
     * Initializes the product
     *
     * @param $name
     * @param $price
     */
    function createFromStrings($price,
                               $name = null,
                               $description = null,
                               $sylius_id = null,
                               $available_on = null,
                               $sku = null,
                               $tax_category = null)
    {
        $this->price = $price;

        if($name)
        {
            $this->name = $name;
        }
        if($description)
        {
            $this->description = $description;
        }
        if($sylius_id)
        {
            $this->sylius_id = $sylius_id;
        }
        if($available_on)
        {
            $this->available_on = $available_on;
        }
        if($sku)
        {
            $this->sku = $sku;
        }
        if($tax_category)
        {
            $this->tax_category = $tax_category;
        }
    }

    /**
     * Method returns string interpretation of sylius product datatype
     *
     * @return string
     */
    function toString()
    {
        return $this->name() .'|#'.
               $this->description() .'|#'.
               $this->price() .'|#'.
               $this->availableOn() .'|#'.
               $this->weight() .'|#'.
               $this->height() .'|#'.
               $this->width() .'|#'.
               $this->sku() .'|#'.
               $this->tax_category();
    }

    /**
     * Fetches the sylius product for the given attribute
     *
     * @param eZContentObjectAttribute $attribute
     */
    function createFromAttribute( $attribute )
    {
        if (!($attribute instanceof eZContentObjectAttribute && is_numeric($attribute->attribute('id')))) {
            return;
        }
        $this->sylius_id = $attribute->attribute('data_int');

        // TODO: should I populate all values here??
    }

    /**
     * Stores the data to ez database
     *
     * @param eZContentObjectAttribute $attribute
     */
    function store( $attribute )
    {
        if ( !( $attribute instanceof eZContentObjectAttribute && is_numeric( $attribute->attribute( 'id' ) ) ) )
        {
            return;
        }

        if ($attribute->content()->sylius_id())
        {
            $attribute->setAttribute('data_int', $attribute->content()->sylius_id());
        }
    }

    /**
     * Returns true if the provided attribute exists
     *
     * @param string $name
     * @return bool
     */
    function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

    /**
     * Returns the specified attribute
     *
     * @param string $name
     * @return mixed
     */
    function attribute( $name )
    {
        switch ( $name )
        {
            case 'price' :
            {
                return $this->price();
            } break;

            case 'name' :
            {
                return $this->name();
            } break;

            case 'sylius_id' :
            {
                return $this->sylius_id();
            } break;

            case 'available_on' :
            {
                return $this->availableOn();
            } break;

            case 'description' :
            {
                return $this->description();
            } break;

            case 'weight' :
            {
                return $this->weight();
            } break;

            case 'height' :
            {
                return $this->height();
            } break;

            case 'width' :
            {
                return $this->width();
            } break;

            case 'sku' :
            {
                return $this->sku();
            } break;

            case 'tax_category' :
            {
                return $this->tax_category();
            } break;

            default:
            {
                eZDebug::writeError( "Attribute '$name' does not exist", "SyliusProduct::attribute" );
                return null;
            } break;
        }
    }

    /**
     * Returns price
     *
     * @return int
     */
    function price()
    {
        return $this->price;
    }

    /**
     * Returns name
     *
     * @return string
     */
    function name()
    {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name
     */
    function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns sylius product id
     *
     * @return int
     */
    function sylius_id()
    {
        return $this->sylius_id;
    }

    /**
     * Sets sylius id
     *
     * @param $id
     */
    function setSyliusId($id)
    {
        $this->sylius_id = $id;
    }

    /**
     * Returns description
     *
     * @return string
     */
    function description()
    {
        return $this->description;
    }

    /**
     * Sets description
     *
     * @param string $description
     */
    function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Sets "available on"
     *
     * @param $available_on
     */
    function setAvailableOn($available_on)
    {
        $this->available_on = $available_on;
    }

    /**
     * Returns "available on" date in 'd-m-Y H:i' format
     *
     * TODO: enable different formats
     *
     * @return string
     */
    function availableOn()
    {
        if ($this->available_on)
            return $this->available_on->format('d-m-Y H:i');
        return null;
    }

    /**
     * Sets price
     *
     * @param $price
     */
    function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Returns slug
     *
     * @return string
     */
    function slug()
    {
        return $this->slug;
    }

    function weight()
    {
        return $this->weight;
    }

    function setWeight($weight)
    {
        $this->weight = $weight;
    }

    function height()
    {
        return $this->height;
    }

    function setHeight($height)
    {
        $this->height = $height;
    }

    function width()
    {
        return $this->width;
    }

    function setWidth($width)
    {
        $this->width = $width;
    }

    function sku()
    {
        return $this->sku;
    }

    function setSku($sku)
    {
        $this->sku = $sku;
    }

    function tax_category()
    {
       return $this->tax_category;
    }

    function setTaxCategory($tax_category)
    {
        $this->tax_category = $tax_category;
    }
}