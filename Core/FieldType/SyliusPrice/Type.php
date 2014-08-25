<?php

namespace Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;


class Type extends FieldType
{

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'syliusprice';
    }

    /**
     * Returns a human readable string representation from the given $value
     * It will be used to generate content name and url alias if current field
     * is designated to be used in the content name/urlAlias pattern.
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice\Value $value
     *
     * @return integer
     */
    public function getName( SPIValue $value )
    {
        return $value->price;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Value|\Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice\Value $value
     *
     * @return integer
     */
    protected function getSortInfo( BaseValue $value )
    {
        return $this->getName ($value);
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return \Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice\Value $value The potentially converted input value.
     */
    protected function createValueFromInput( $inputValue )
    {
        if (is_int($inputValue))
        {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice\Value $value
     */
    protected function checkValueStructure( BaseValue $value )
    {
        if (!is_int($value))
        {
            throw new InvalidArgumentType(
                '$value->price',
                'integer',
                $value->price);
        }
    }

    /**
     * Returns the empty value for this field type.
     *
     * @return \Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @throws \Exception
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function fromHash( $hash )
    {
        if($hash === null)
        {
            return $this->getEmptyValue();
        }
        else{
            $price = $hash['price'];
            return new Value($price);
        }
    }

    /**
     * Converts the given $value into a plain hash format
     *
     * @param \eZ\Publish\SPI\FieldType\Value|\Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice\Value $value
     *
     * @return mixed
     */
    public function toHash( SPIValue $value )
    {
        return array( 'price' => $value->price );
    }

    /**
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusPrice\Value $value
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( SPIValue $value )
    {
        return new FieldValue(
            array(
                "data" => $value->price,
                "externalData" => null,
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     * @return \Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->data === null )
        {
            return $this->getEmptyValue();
        }
        return new Value( $fieldValue->data );
    }

}