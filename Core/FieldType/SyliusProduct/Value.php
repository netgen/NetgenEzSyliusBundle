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


    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}

