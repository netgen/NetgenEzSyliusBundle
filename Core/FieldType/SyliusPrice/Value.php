<?php

namespace Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    public $price;

    public function __toString()
    {
        return (string)$this->price;
    }

    public function __construct($price = null)
    {
        $this->price = $price;
    }
}

