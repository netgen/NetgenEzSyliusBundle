<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;

use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use RuntimeException;
use PDO;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $connection;

    /**
     * Sets the data storage connection to use
     *
     * @throws \RuntimeException if $connection is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $connection
     */
    public function setConnection( $connection )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$connection instanceof DatabaseHandler )
        {
            throw new RuntimeException( "Invalid connection passed" );
        }

        $this->connection = $connection;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ( $this->connection === null )
        {
            throw new RuntimeException( "Missing database connection." );
        }

        return $this->connection;
    }

    /**
     * Stores the data in the database based on the given field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param int $productId
     */
    public function storeFieldData( VersionInfo $versionInfo, $productId )
    {
        $connection = $this->getConnection();

        $contentId = $versionInfo->contentInfo->id;

        $selectQuery = $connection->createSelectQuery();
        $selectQuery
            ->selectDistinct( $connection->quoteColumn( "contentobject_id", "ngsyliusproduct" ) )
            ->from( $connection->quoteTable( "ngsyliusproduct" ) )
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn( "contentobject_id", "ngsyliusproduct" ),
                    $selectQuery->bindValue( $contentId, null, PDO::PARAM_INT )
                )
            );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        if ( count( $rows ) > 0 )
        {
            $updateQuery = $connection->createUpdateQuery();
            $updateQuery
                ->update( "ngsyliusproduct" )
                ->set(
                    $connection->quoteColumn( "product_id" ),
                    $updateQuery->bindValue( $productId, null, PDO::PARAM_INT )
                )
                ->where(
                    $updateQuery->expr->eq(
                        $connection->quoteColumn( "contentobject_id", "ngsyliusproduct" ),
                        $updateQuery->bindValue( $contentId, null, PDO::PARAM_INT )
                    )
                );
        }
        else
        {
            $insertQuery = $connection->createInsertQuery();
            $insertQuery
                ->insertInto( $connection->quoteTable( "ngsyliusproduct" ) )
                ->set(
                    $connection->quoteColumn( "contentobject_id" ),
                    $insertQuery->bindValue( $contentId, null, PDO::PARAM_INT )
                )->set(
                    $connection->quoteColumn( "product_id" ),
                    $insertQuery->bindValue( $productId, null, PDO::PARAM_INT )
                );

            $insertQuery->prepare()->execute();
        }
    }

    /**
     * Gets the product ID stored in the field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @return int product ID
     */
    public function getFieldData( VersionInfo $versionInfo )
    {
        return $this->loadFieldData( $versionInfo->contentInfo->id );
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds )
    {
        $connection = $this->getConnection();

        $query = $connection->createDeleteQuery();
        $query
            ->deleteFrom( $connection->quoteTable( "eztags_attribute_link" ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->in(
                        $connection->quoteColumn( "objectattribute_id" ),
                        $fieldIds
                    ),
                    $query->expr->eq(
                        $connection->quoteColumn( "objectattribute_version" ),
                        $query->bindValue( $versionInfo->versionNo, null, PDO::PARAM_INT )
                    )
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Returns true if content and product ID match the data in the database, false otherwise
     *
     * @param VersionInfo $versionInfo
     * @param int $productId
     *
     * @return bool
     */
    public function checkFieldData( VersionInfo $versionInfo, $productId )
    {
        $connection = $this->getConnection();

        $query = $connection->createSelectQuery();
        $query
            ->select( "*" )
            ->from( $connection->quoteTable( "ngsyliusproduct" ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $connection->quoteColumn( "contentobject_id", "ngsyliusproduct" ),
                        $query->bindValue( $versionInfo->contentInfo->id, null, PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $connection->quoteColumn( "product_id", "ngsyliusproduct" ),
                        $query->bindValue( $productId, null, PDO::PARAM_INT )
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        return count( $rows ) > 0 ? true : false;
    }

    /**
     * Returns the data for the given $fieldId and $versionNo
     *
     * @param int $contentId
     *
     * @return array
     */
    protected function loadFieldData( $contentId )
    {
        $connection = $this->getConnection();

        $query = $connection->createSelectQuery();
        $query
            ->selectDistinct( $connection->quoteColumn( "product_id", "ngsyliusproduct" ) )
            ->from( $connection->quoteTable( "ngsyliusproduct" ) )
            ->where(
                $query->expr->eq(
                    $connection->quoteColumn( "contentobject_id", "ngsyliusproduct" ),
                    $query->bindValue( $contentId, null, PDO::PARAM_INT )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( PDO::FETCH_ASSOC );

        return empty( $rows[0]["product_id"] ) ? null : $rows[0]["product_id"];
    }
}
