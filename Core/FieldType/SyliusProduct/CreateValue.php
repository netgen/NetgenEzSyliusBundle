<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class CreateValue extends  BaseValue
{
    public $createArray = array(
        'name' => null,
        'description' => null,
        'price' => null,
        'available_on' => null,
        'weight' => null,
        'height' => null,
        'width' => null,
        'depth' => null,
        'sku' => null,
        'tax_category' => null
    );

    public function __construct( array $valueArray )
    {
        $this->createArray = array_merge( $this->createArray, $valueArray );
    }

    public function __toString()
    {
        return json_encode( $this->createArray );
    }
}
