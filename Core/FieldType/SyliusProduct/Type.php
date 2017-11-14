<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class Type extends FieldType
{
    /**
     * @var \Sylius\Component\Resource\Repository\RepositoryInterface
     */
    protected $productRepository;

    /**
     * Constructor.
     *
     * @param \Sylius\Component\Resource\Repository\RepositoryInterface $productRepository
     */
    public function __construct(RepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'syliusproduct';
    }

    /**
     * Returns a human readable string representation from the given $value.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string) $value;
    }

    /**
     * Returns the empty value for this field type.
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function fromHash($hash)
    {
        if (!is_int($hash)) {
            return $this->getEmptyValue();
        }

        return $this->createValueFromInput($hash);
    }

    /**
     * Converts the given $value into a plain hash format.
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value|\eZ\Publish\SPI\FieldType\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if (!$value->product instanceof ProductInterface) {
            return null;
        }

        return $value->product->getId();
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value|\eZ\Publish\SPI\FieldType\Value $value The value of the field type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue the value processed by the storage engine
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            array(
                'data' => null,
                'externalData' => $value->product,
                'sortKey' => false,
            )
        );
    }

    /**
     * Converts a persistence $value to a Value.
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        return new Value($fieldValue->externalData);
    }

    /**
     * Indicates if the field type supports indexing and sort keys for searching.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return mixed
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value The potentially converted input value
     */
    protected function createValueFromInput($inputValue)
    {
        if (!is_int($inputValue)) {
            return $inputValue;
        }

        return new Value(
            $this->productRepository->find($inputValue)
        );
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     *
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value|\eZ\Publish\Core\FieldType\Value $value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if ($value->product !== null && !$value->product instanceof ProductInterface) {
            throw new InvalidArgumentType(
                '$value->product',
                ProductInterface::class,
                $value->product
            );
        }
    }
}
