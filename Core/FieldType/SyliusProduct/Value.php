<?php

namespace Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    public $price;
    public $name="";
    public $syliusId = 0;
    public $slug="";
    public $description="";
    public $available_on=null;

    public function __construct($price = null, $name = null, $syliusId = null)
    {
        if($price)
        {
            $this->price = $price;
        }
        if($name)
        {
            $this->price = $name;
        }
        if($syliusId)
        {
            $this->price = $syliusId;
        }
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->price . '|#' . $this->name . '|#' . $this->syliusId;
    }
}

