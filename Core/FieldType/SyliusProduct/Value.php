<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use Sylius\Component\Product\Model\ProductInterface;
use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * @var array
     */
    protected $dynamicProperties = array(
        'productId',
        'code',
        'price',
        'name',
        'description',
    );

    /**
     * @var \Sylius\Component\Core\Model\Product
     */
    public $product;

    /**
     * @var array
     */
    public $productData;

    /**
     * Constructor.
     *
     * @param \Sylius\Component\Product\Model\ProductInterface $product
     * @param array $productData
     */
    public function __construct(ProductInterface $product = null, array $productData = null)
    {
        $this->product = $product;
        $this->productData = $productData;
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

    /**
     * Magic get function handling read to non public properties.
     *
     * @param string $property Name of the property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (!in_array($property, $this->dynamicProperties)) {
            return parent::__get($property);
        }

        if (is_array($this->productData) && isset($this->productData[$property])) {
            return $this->productData['property'];
        }

        if (!$this->product instanceof ProductInterface) {
            return null;
        }

        switch ($property) {
            case 'productId':
                return $this->product->getId();
            case 'code':
                return $this->product->getCode();
            case 'price':
                return $this->product->getPrice();
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
        if (in_array($property, $this->dynamicProperties)) {
            return true;
        }

        return parent::__isset($property);
    }
}
