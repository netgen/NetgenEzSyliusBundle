<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;


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
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     *
     * @return integer
     */
    public function getName( SPIValue $value )
    {
        return $value->name;
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
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     *
     * @return bool
     */
    protected function getSortInfo( BaseValue $value )
    {
        return $value->price;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value The potentially converted input value.
     */
    protected function createValueFromInput( $inputValue )
    {
        if ( $inputValue instanceof Value ){
            return $inputValue;
        }
        elseif ( is_array($inputValue) )
        {
            $newValue = $this->fromHash($inputValue);
            return $newValue;
        }
        elseif ( is_int($inputValue) )
        {
            /** @var \Sylius\Component\Core\Model\Product $product */
            $product = $this->syliusRepository->find($inputValue);
            $newValue = new Value( array(
                    'price' =>  $product->getPrice(),
                    'sylius_id' => $product->getId(),
                    'name' =>   $product->getName(),
                    'description' =>    $product->getDescription(),
                    'available_on' =>   $product->getAvailableOn(),
                    'weight' => $product->getMasterVariant()->getWeight(),
                    'height' => $product->getMasterVariant()->getHeight(),
                    'width' => $product->getMasterVariant()->getWidth(),
                    'depth' => $product->getMasterVariant()->getDepth(),
                    'sku' => $product->getSku(),
                    'tax_category' => null
                )
            );

            if ( $product->getTaxCategory() )
                $newValue->tax_category = $product->getTaxCategory();

            return $newValue;
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     */
    protected function checkValueStructure( BaseValue $value )
    {
        if ( !is_int($value->price) && $value->price < 0 )
        {
            throw new InvalidArgumentType(
                '$value->price',
                'integer',
                $value->price);
        }
        if( !is_string($value->name) )
        {
            throw new InvalidArgumentType(
                '$value->name',
                'string',
                $value->name
            );
        }
        if( !is_string($value->description) )
        {
            throw new InvalidArgumentType(
                '$value->description',
                'string',
                $value->description
            );
        }
        if( !is_numeric($value->height) && $value->height !== null )
        {
            throw new InvalidArgumentType(
                '$value->height',
                'integer',
                $value->height
            );
        }
        if( !is_numeric($value->weight) && $value->weight !== null )
        {
            throw new InvalidArgumentType(
                '$value->weight',
                'integer',
                $value->weight
            );
        }
        if( !is_numeric($value->width) && $value->width !== null )
        {
            throw new InvalidArgumentType(
                '$value->width',
                'integer',
                $value->width
            );
        }
        if( !is_numeric($value->depth) && $value->depth !== null )
        {
            throw new InvalidArgumentType(
                '$value->depth',
                'integer',
                $value->depth
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
     * @return \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value
     */
    public function fromHash( $hash )
    {
        if ( !is_array( $hash ) || empty($hash['price']) )
        {
            return new Value();
        }

        $value = new Value();

        if (!empty($hash['price']))
            $value->price = $hash['price'];

        if (!empty($hash['name']))
            $value->name = $hash['name'];

        if (!empty($hash['sylius_id']))
            $value->syliusId = $hash['sylius_id'];

        if (!empty($hash['description']))
            $value->description = $hash['description'];

        if (!empty($hash['slug']))
            $value->slug = $hash['slug'];

        if (!empty($hash['available_on']))
            $value->available_on = $hash['available_on'];

        if (!empty($hash['weight']))
            $value->weight = $hash['weight'];

        if (!empty($hash['height']))
            $value->height = $hash['height'];

        if (!empty($hash['width']))
            $value->width = $hash['width'];

        if (!empty($hash['depth']))
            $value->depth = $hash['depth'];

        if (!empty($hash['sku']))
            $value->sku = $hash['sku'];

        if (!empty($hash['tax_category']))
            $value->tax_category = $hash['tax_category'];

        return $value;
    }

    /**
     * Converts the given $value into a plain hash format
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     *
     * @return array
     */
    public function toHash( SPIValue $value )
    {
        if (empty($value->price) || empty($value->name) || empty($value->syliusId) )
            return array();

        return array( 'price' => $value->price,
                      'name' => $value->name,
                      'sylius_id' => $value->syliusId,
                      'description' => $value->description,
                      'slug' => $value->slug,
                      'available_on' => $value->available_on,
                      'weight' => $value->weight,
                      'height' => $value->height,
                      'width' => $value->width,
                      'depth' => $value->depth,
                      'sku' => $value->sku,
                      'tax_category' => $value->tax_category);
    }

    /**
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( SPIValue $value )
    {
        return new FieldValue(
            array(
                "data" => $this->ezToHash($value),
                "externalData" => $this->syliusToHash($value),
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     * @return \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->data === null )
        {
            return $this->getEmptyValue();
        }
        $value = new Value(
            array(
                'name' => $fieldValue->externalData['name'],
                'price' => $fieldValue->externalData['price'],
                'description' => $fieldValue->externalData['description'],
                'slug' => $fieldValue->externalData['slug'],
                'syliusId' => $fieldValue->data['sylius_id'],
                'available_on' => $fieldValue->externalData['available_on'],
                'weight' => $fieldValue->externalData['weight'],
                'height' => $fieldValue->externalData['height'],
                'width' => $fieldValue->externalData['width'],
                'depth' => $fieldValue->externalData['depth'],
                'sku' => $fieldValue->externalData['sku'],
                'tax_category' => $fieldValue->externalData['tax_category']
            )
        );

        return $value;
    }

    /**
     * Returns hash of values to be stored in eZ database
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     * @return array
     */
    protected function ezToHash($value)
    {
        return array(
            'sylius_id' => $value->syliusId
        );
    }

    /**
     * Returns hash of values to be stored in sylius database
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     * @return array
     */
    protected function syliusToHash($value)
    {
        return array(
            'name' => $value->name,
            'price' => $value->price,
            'description' => $value->description,
            'slug' => $value->slug,
            'available_on' => $value->available_on,
            'weight' => $value->weight,
            'height' => $value->height,
            'width' => $value->width,
            'depth' => $value->depth,
            'sku' => $value->sku,
            'tax_category' => $value->tax_category
        );
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
