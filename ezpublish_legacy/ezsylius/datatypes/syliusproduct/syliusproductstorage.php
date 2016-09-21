<?php

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;

class SyliusProductStorage
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Type
     */
    protected $fieldType;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage
     */
    protected $externalStorage;

    /**
     * @var array
     */
    protected $context;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->container = ezpKernel::instance()->getServiceContainer();

        $this->fieldType = $this->container->get('netgen_ez_sylius.field_type.syliusproduct');
        $this->contentHandler = $this->container->get('ezpublish.spi.persistence.content_handler');
        $this->externalStorage = $this->container->get('netgen_ez_sylius.field_type.syliusproduct.external_storage');

        $this->context = array(
            'identifier' => 'LegacyStorage',
            'connection' => $this->container->get('ezpublish.api.storage_engine.legacy.dbhandler'),
        );
    }

    public function storeFieldData(eZContentObjectAttribute $objectAttribute, Value $value)
    {
        $versionInfo = $this->getVersionInfo(
            $objectAttribute->attribute('contentobject_id'),
            $objectAttribute->attribute('version')
        );

        $field = $this->getField($objectAttribute, $value);

        return $this->externalStorage->storeFieldData($versionInfo, $field, $this->context);
    }

    public function getFieldData(eZContentObjectAttribute $objectAttribute)
    {
        $versionInfo = $this->getVersionInfo(
            $objectAttribute->attribute('contentobject_id'),
            $objectAttribute->attribute('version')
        );

        $field = $this->getField($objectAttribute);
        $this->externalStorage->getFieldData($versionInfo, $field, $this->context);

        return $this->fieldType->fromPersistenceValue($field->value);
    }

    public function deleteFieldData(eZContentObjectAttribute $objectAttribute, $version = null)
    {
        $versionNos = array($version);
        $fieldIds = array((int)$objectAttribute->attribute('id'));

        if ($version === null) {
            $objectVersions = eZContentObjectVersion::fetchObjectList(
                eZContentObjectVersion::definition(),
                null,
                array(
                    'contentobject_id' => $objectAttribute->attribute('contentobject_id'),
                )
            );

            $versionNos = array_map(
                function (eZContentObjectVersion $objectVersion) {
                    return $objectVersion->attribute('version');
                },
                $objectVersions
            );
        }

        foreach ($versionNos as $versionNo) {
            $versionInfo = $this->getVersionInfo(
                $objectAttribute->attribute('contentobject_id'),
                $versionNo
            );

            $this->externalStorage->deleteFieldData($versionInfo, $fieldIds, $this->context);
        }
    }

    /**
     * Returns an SPI VersionInfo object used by external storage.
     *
     * @param int $contentId
     * @param int $versionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    protected function getVersionInfo($contentId, $versionNo)
    {
        return new VersionInfo(
            array(
                'versionNo' => $versionNo,
                'contentInfo' => new ContentInfo(
                    array(
                        'id' => $contentId,
                    )
                ),
            )
        );
    }

    /**
     * Returns a field converted from object attribute.
     *
     * @param \eZContentObjectAttribute $objectAttribute
     * @param \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    protected function getField(eZContentObjectAttribute $objectAttribute, Value $value = null)
    {
        $fieldValue = new FieldValue(
            array(
                'data' => $value instanceof Value ? $value->productData : null,
                'externalData' => $value instanceof Value ? $value->product : null,
                'sortKey' => false,
            )
        );

        return new Field(
            array(
                'id' => (int)$objectAttribute->attribute('id'),
                'fieldDefinitionId' => (int)$objectAttribute->attribute('contentclassattribute_id'),
                'type' => $objectAttribute->attribute('data_type_string'),
                'value' => $fieldValue,
                'languageCode' => $objectAttribute->attribute('language_code'),
                'versionNo' => (int)$objectAttribute->attribute('version'),
            )
        );
    }
}
