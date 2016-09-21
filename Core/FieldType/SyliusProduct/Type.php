<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

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
        return (string)$value;
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
        if (is_array($inputValue)) {
            $product = null;

            if (!empty($inputValue['productId'])) {
                $product = $this->productRepository->find($inputValue['productId']);

                if (!$product instanceof ProductInterface) {
                    unset($inputValue['productId']);
                }
            }

            return new Value($product, $inputValue);
        }

        if (is_int($inputValue)) {
            $product = $this->productRepository->find($inputValue);

            return new Value($product);
        }

        return $inputValue;
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

        $productData = $value->productData;

        if ($productData !== null && !is_array($productData)) {
            throw new InvalidArgumentType(
                '$value->productData',
                'array',
                $productData
            );
        }

        if (!is_array($productData)) {
            return;
        }

        if (!isset($productData['code']) || !is_string($productData['code'])) {
            throw new InvalidArgumentType(
                '$value->productData["code"]',
                'string',
                isset($productData['code']) ? $productData['code'] : null
            );
        }

        if (!isset($productData['price']) || !is_int($productData['price'])) {
            throw new InvalidArgumentType(
                '$value->productData["price"]',
                'int',
                isset($productData['price']) ? $productData['price'] : null
            );
        }

        if (!isset($productData['name']) || !is_string($productData['name'])) {
            throw new InvalidArgumentType(
                '$value->productData["name"]',
                'string',
                isset($productData['name']) ? $productData['name'] : null
            );
        }

        if (isset($productData['description']) && !is_string($productData['description'])) {
            throw new InvalidArgumentType(
                '$value->productData["description"]',
                'string',
                isset($productData['description']) ? $productData['description'] : null
            );
        }
    }

    /**
     * Validates a field based on the validator configuration in the field definition.
     *
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value|\eZ\Publish\SPI\FieldType\Value $value The field value for which an action is performed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDef, SPIValue $value)
    {
        $validationErrors = array();

        if (!is_array($value->productData)) {
            return $validationErrors;
        }

        if (empty($value->productData['code']) && empty($value->productData['price'])) {
            if ($fieldDef->isRequired) {
                $validationErrors[] = new ValidationError('Product code and price must be specified');
            }

            return $validationErrors;
        }

        if (empty($value->productData['code'])) {
            $validationErrors[] = new ValidationError('Product code cannot be empty');
        }

        if ($this->productCodeExists($value->productData['code'], $value->product)) {
            $validationErrors[] = new ValidationError('Product with specified code already exists');
        }

        if (empty($value->productData['price'])) {
            $validationErrors[] = new ValidationError('Product price cannot be empty');
        }

        if ($value->productData['price'] < 0) {
            $validationErrors[] = new ValidationError('Product price cannot be negative');
        }

        if (empty($value->productData['name'])) {
            $validationErrors[] = new ValidationError('Product name cannot be empty');
        }

        return $validationErrors;
    }

    protected function productCodeExists($code, ProductInterface $product = null)
    {
        $productByCode = $this->productRepository->findOneBy(
            array(
                'code' => $code,
            )
        );

        if (!$productByCode instanceof ProductInterface) {
            return false;
        }

        if ($product instanceof ProductInterface) {
            if ($productByCode->getId() == $product->getId()) {
                return false;
            }
        }

        return true;
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
        if (!is_array($hash)) {
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
            return array();
        }

        return array(
            'productId' => $value->product->getId(),
            'code' => $value->product->getCode(),
            'price' => $value->product->getPrice(),
            'name' => $value->product->getName(),
            'description' => $value->product->getDescription(),
        );
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
                'data' => $value->productData,
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
        return new Value($fieldValue->externalData, $fieldValue->data);
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
}
