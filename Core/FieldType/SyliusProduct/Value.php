<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    public $price;
    public $name="";
    public $syliusId = 0;
    public $slug="";
    public $description="";
    public $available_on=null;
    public $weight = null;
    public $height = null;
    public $width = null;
    public $depth = null;
    public $sku = null;
    public $tax_category = null;

    public function __construct($price = null,
                                $name = null,
                                $description = null,
                                $availableOn = null,
                                $weight = null,
                                $height = null,
                                $width = null,
                                $depth = null,
                                $sku = null,
                                $taxCategory = null)
    {
        if($price)
        {
            $this->price = $price;
        }
        if($name)
        {
            $this->name = $name;
        }
        if($description)
        {
            $this->description = $description;
        }
        if($availableOn)
        {
            $this->available_on = $availableOn;
        }
        if($weight)
        {
            $this->weight = $weight;
        }
        if($height)
        {
            $this->height = $height;
        }
        if($width)
        {
            $this->width = $width;
        }
        if($depth)
        {
            $this->depth = $depth;
        }
        if($sku)
        {
            $this->sku = $sku;
        }
        if($taxCategory)
        {
            $this->tax_category = $taxCategory;
        }
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name .'|#'.
                $this->description .'|#'.
                $this->price .'|#'.
                $this->availableOn .'|#'.
                $this->weight .'|#'.
                $this->height .'|#'.
                $this->width .'|#'.
                $this->depth .'|#'.
                $this->sku .'|#'.
                $this->tax_category;
    }
}

