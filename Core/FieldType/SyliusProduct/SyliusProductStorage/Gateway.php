<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage;

use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

abstract class Gateway extends StorageGateway
{
    /**
     * Stores the data in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param int $productId
     */
    abstract public function storeFieldData(VersionInfo $versionInfo, $productId);

    /**
     * Gets the product ID stored in the field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @return int product ID
     */
    abstract public function getFieldData(VersionInfo $versionInfo);

    /**
     * Deletes field data for content id identified by $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    abstract public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds);
}
