<?php

use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;

class SyliusProductType extends eZDataType
{
    const DATA_TYPE_STRING = 'syliusproduct';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Type
     */
    protected $fieldType;

    /**
     * @var \SyliusProductStorage
     */
    protected $storage;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            self::DATA_TYPE_STRING,
            ezpI18n::tr('extension/ezsylius/datatypes', 'Sylius product')
        );

        $this->container = ezpKernel::instance()->getServiceContainer();

        $this->fieldType = $this->container->get('netgen_ez_sylius.field_type.syliusproduct');

        $this->storage = new SyliusProductStorage();
    }

    /**
     * Initializes content object attribute based on another attribute.
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param int $currentVersion
     * @param eZContentObjectAttribute $originalContentObjectAttribute
     */
    public function initializeObjectAttribute($objectAttribute, $currentVersion, $originalContentObjectAttribute)
    {
        $value = $currentVersion != false ?
            $originalContentObjectAttribute->content() :
            $this->fieldType->getEmptyValue();

        $objectAttribute->setContent($value);
    }

    /**
     * Deletes the object attribute.
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param int $version
     */
    public function deleteStoredObjectAttribute($objectAttribute, $version = null)
    {
        if ($version === null) {
            $this->storage->deleteFieldData($objectAttribute, $version);
        }
    }

    /**
     * Returns true if content object attribute has content.
     *
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return bool
     */
    public function hasObjectAttributeContent($objectAttribute)
    {
        $value = $objectAttribute->content();
        if (!$value instanceof Value) {
            return false;
        }

        return !$this->fieldType->isEmptyValue($value);
    }

    /**
     * Returns the content.
     *
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return mixed
     */
    public function objectAttributeContent($objectAttribute)
    {
        return $this->storage->getFieldData($objectAttribute);
    }

    /**
     * Stores the datatype data to the database which is related to the object attribute.
     *
     * @param eZContentObjectAttribute $objectAttribute
     */
    public function storeObjectAttribute($objectAttribute)
    {
        /** @var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value */
        $value = $objectAttribute->content();

        $this->storage->storeFieldData($objectAttribute, $value);
    }

    /**
     * Validates the input and returns true if the input was valid for this datatype.
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return bool
     */
    public function validateObjectAttributeHTTPInput($http, $base, $objectAttribute)
    {
        $productId = $this->getProductIdFromInput($http, $base, $objectAttribute);

        try {
            $value = $this->fieldType->acceptValue($productId);
        } catch (InvalidArgumentException $e) {
            $objectAttribute->setValidationError($e->getMessage());

            return eZInputValidator::STATE_INVALID;
        }

        $validationErrors = $this->fieldType->validate(
            $this->getFieldDefinition($objectAttribute),
            $value
        );

        if (empty($validationErrors)) {
            return eZInputValidator::STATE_ACCEPTED;
        }

        $errorMessages = array();
        foreach ($validationErrors as $validationError) {
            $errorMessages[] = $validationError->getTranslatableMessage()->message;
        }

        $objectAttribute->setValidationError(implode(', ', $errorMessages));

        return eZInputValidator::STATE_INVALID;
    }

    /**
     * Returns the field definition object built from provided content object attribute.
     *
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    protected function getFieldDefinition($objectAttribute)
    {
        return new FieldDefinition(
            array(
                'isRequired' => $objectAttribute->contentClassAttributeIsRequired(),
            )
        );
    }

    /**
     * Fetches the HTTP input for the content object attribute.
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return bool
     */
    public function fetchObjectAttributeHTTPInput($http, $base, $objectAttribute)
    {
        $productId = $this->getProductIdFromInput($http, $base, $objectAttribute);

        $objectAttribute->setContent(
            $this->fieldType->fromHash($productId)
        );

        return true;
    }

    /**
     * Fetches the product ID from HTTP input for the content object attribute.
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return int
     */
    protected function getProductIdFromInput($http, $base, $objectAttribute)
    {
        return (int) $http->postVariable($base . '_data_product_id_' . $objectAttribute->attribute('id'));
    }

    /**
     * Will return information on how the datatype should be represented in
     * the various display modes when used by an object.
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param array|bool $mergeInfo
     *
     * @return array
     */
    public function objectDisplayInformation($objectAttribute, $mergeInfo = false)
    {
        $info = array(
            'edit' => array(
                'grouped_input' => true,
            ),
            'view' => array(
                'grouped_input' => true,
            ),
        );

        return parent::objectDisplayInformation($objectAttribute, $info);
    }
}

eZDataType::register(SyliusProductType::DATA_TYPE_STRING, 'SyliusProductType');
