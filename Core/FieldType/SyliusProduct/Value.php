<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use Sylius\Component\Product\Model\ProductInterface;

class Value extends BaseValue
{
    /**
     * @var \Sylius\Component\Core\Model\Product
     */
    public $product;

    /**
     * @var array
     */
    protected $dynamicProperties = array(
        'productId',
        'code',
        'name',
        'description',
    );

    /**
     * Constructor.
     *
     * @param \Sylius\Component\Product\Model\ProductInterface $product
     */
    public function __construct(ProductInterface $product = null)
    {
        $this->product = $product;
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->product instanceof ProductInterface) {
            return $this->product->getName();
        }

        return '';
    }

    /**
     * Magic get function handling read to non public properties.
     *
     * @param string $property Name of the property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (!in_array($property, $this->dynamicProperties, true)) {
            return parent::__get($property);
        }

        if (!$this->product instanceof ProductInterface) {
            return null;
        }

        switch ($property) {
            case 'productId':
                return $this->product->getId();
            case 'code':
                return $this->product->getCode();
            case 'name':
                return $this->product->getName();
            case 'description':
                return $this->product->getDescription();
        }

        return null;
    }

    /**
     * Magic isset function handling isset() to non public properties.
     *
     * @param string $property Name of the property
     *
     * @return bool
     */
    public function __isset($property)
    {
        if (in_array($property, $this->dynamicProperties, true)) {
            return true;
        }

        return parent::__isset($property);
    }

    /**
     * Function where list of properties are returned.
     *
     * @param array $dynamicProperties Additional dynamic properties exposed on the object
     *
     * @return array
     */
    protected function getProperties($dynamicProperties = array())
    {
        $dynamicProperties = array_merge($dynamicProperties, $this->dynamicProperties);

        return parent::getProperties($dynamicProperties);
    }
}
