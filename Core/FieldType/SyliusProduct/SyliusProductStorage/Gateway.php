<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\StorageGateway;

abstract class Gateway extends StorageGateway
{
    /**
     * Stores the data in the database based on the given field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param int $productId
     */
    abstract public function storeFieldData( VersionInfo $versionInfo, $productId );

    /**
     * Gets the product id stored in the field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @returns int product id
     */
    abstract public function getFieldData( VersionInfo $versionInfo );

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    abstract public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds );

    /**
     * Returns true if content and product id match the data in the database, false otherwise
     *
     * @param VersionInfo $versionInfo
     * @param int $productId
     * @return bool
     */
    abstract public function checkFieldData( VersionInfo $versionInfo, $productId );
}
