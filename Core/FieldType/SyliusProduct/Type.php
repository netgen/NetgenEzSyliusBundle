<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value;
use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\CreateValue;

class Type extends FieldType
{
    protected $syliusRepository;

    public function __construct( RepositoryInterface $syliusRepository )
    {
        $this->syliusRepository = $syliusRepository;
    }

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'syliusproduct';
    }

    /**
     * Returns a human readable string representation from the given $value
     * It will be used to generate content name and url alias if current field
     * is designated to be used in the content name/urlAlias pattern.
     *
     * @param SPIValue $value
     *
     * @return integer
     */
    public function getName( SPIValue $value )
    {
        if( !empty( $value->product ) )
        {
            return $value->product->getName();
        }

        return '';
    }

    /**
     * Returns the empty value for this field type.
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param BaseValue $value
     *
     * @return bool
     */
    protected function getSortInfo( BaseValue $value )
    {
        return false;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return Value|CreateValue $value The potentially converted input value.
     */
    protected function createValueFromInput( $inputValue )
    {
        if ( $inputValue instanceof Value || $inputValue instanceof CreateValue )
        {
            return $inputValue;
        }
        else if ( is_array( $inputValue ) )
        {
            return new CreateValue( $inputValue );
        }
        else if ( is_int( $inputValue ) )
        {
            /** @var \Sylius\Component\Core\Model\Product $product */
            $product = $this->syliusRepository->find( $inputValue );

            $newValue = new Value();
            $newValue->product = $product;

            return $newValue;
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param BaseValue $value
     */
    protected function checkValueStructure( BaseValue $value )
    {
        if ( $value instanceof Value && !( $value->product instanceof ProductInterface ) )
        {
            throw new InvalidArgumentType(
                '$value',
                'Sylius\Component\Core\Model\ProductInterface',
                $value->product
            );
        }
        elseif ( $value instanceof CreateValue && !is_array( $value->createArray ) )
        {
            throw new InvalidArgumentType(
                '$value',
                'array',
                $value->createArray
            );
        }
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @throws \Exception
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\CreateValue
     */
    public function fromHash( $hash )
    {
        if ( !is_array( $hash ) )
        {
            return $this->getEmptyValue();
        }

        $value = new CreateValue( $hash );

        return $value;
    }

    /**
     * Converts the given $value into a plain hash format
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value|SPIValue $value
     *
     * @return array
     */
    public function toHash( SPIValue $value )
    {
        if( $value->product === null )
        {
            return array();
        }

        return array(
            'price' => $value->product->getPrice(),
            'name' => $value->product->getName(),
            'description' => $value->product->getDescription(),
            'slug' => $value->product->getSlug(),
            'available_on' => $value->product->getAvailableOn(),
            'weight' => $value->product->getMasterVariant()->getWeight(),
            'height' => $value->product->getMasterVariant()->getHeight(),
            'width' => $value->product->getMasterVariant()->getWidth(),
            'depth' => $value->product->getMasterVariant()->getDepth(),
            'sku' => $value->product->getSku(),
            'tax_category' => $value->product->getTaxCategory()->getName()
        );
    }

    /**
     * @param SPIValue $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( SPIValue $value )
    {
        if( $value instanceof Value )
        {
            return new FieldValue(
                array(
                    "data" => $value->product->getId(),
                    "externalData" => $value->product,
                    "sortKey" => $this->getSortInfo( $value ),
                )
            );
        }
        elseif( $value instanceof CreateValue )
        {
            return new FieldValue(
                array(
                    "data" => null,
                    "externalData" => $value->createArray,
                    "sortKey" => $this->getSortInfo( $value ),
                )
            );
        }
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->externalData === null || !( $fieldValue->externalData instanceof ProductInterface)  )
        {
            return $this->getEmptyValue();
        }

        $value = new Value();
        $value->product = $fieldValue->externalData;

        return $value;
    }

    static protected function checkValueType( $value )
    {
        if ( !$value instanceof Value && !$value instanceof CreateValue )
        {
            throw new InvalidArgumentType(
                "\$value",
                "Netgen\\Bundle\\EzSyliusBundle\\Core\\FieldType\\SyliusProduct\Value or Netgen\\Bundle\\EzSyliusBundle\\Core\\FieldType\\SyliusProduct\\CreateValue",
                $value );
        }
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return false;
    }
}
