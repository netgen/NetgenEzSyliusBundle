<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;
use PDO;
use RuntimeException;

class LegacyStorage extends Gateway
{
    /**
     * Connection.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $connection;

    /**
     * Sets the data storage connection to use.
     *
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $connection
     *
     * @throws \RuntimeException if $connection is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection($connection)
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if (!$connection instanceof DatabaseHandler) {
            throw new RuntimeException('Invalid connection passed');
        }

        $this->connection = $connection;
    }

    /**
     * Stores the data in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param int $productId
     */
    public function storeFieldData(VersionInfo $versionInfo, $productId)
    {
        $connection = $this->getConnection();

        $contentId = $versionInfo->contentInfo->id;

        $selectQuery = $connection->createSelectQuery();
        $selectQuery
            ->selectDistinct($connection->quoteColumn('contentobject_id', 'ngsyliusproduct'))
            ->from($connection->quoteTable('ngsyliusproduct'))
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('contentobject_id', 'ngsyliusproduct'),
                    $selectQuery->bindValue($contentId, null, PDO::PARAM_INT)
                )
            );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $query = $connection->createUpdateQuery();
            $query
                ->update('ngsyliusproduct')
                ->set(
                    $connection->quoteColumn('product_id'),
                    $query->bindValue($productId, null, PDO::PARAM_INT)
                )
                ->where(
                    $query->expr->eq(
                        $connection->quoteColumn('contentobject_id', 'ngsyliusproduct'),
                        $query->bindValue($contentId, null, PDO::PARAM_INT)
                    )
                )
            ;
        } else {
            $query = $connection->createInsertQuery();
            $query
                ->insertInto($connection->quoteTable('ngsyliusproduct'))
                ->set(
                    $connection->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId, null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn('product_id'),
                    $query->bindValue($productId, null, PDO::PARAM_INT)
                )
            ;
        }

        $query->prepare()->execute();
    }

    /**
     * Gets the product ID stored in the field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @return int product ID
     */
    public function getFieldData(VersionInfo $versionInfo)
    {
        $connection = $this->getConnection();

        $query = $connection->createSelectQuery();
        $query
            ->selectDistinct($connection->quoteColumn('product_id', 'ngsyliusproduct'))
            ->from($connection->quoteTable('ngsyliusproduct'))
            ->where(
                $query->expr->eq(
                    $connection->quoteColumn('contentobject_id', 'ngsyliusproduct'),
                    $query->bindValue($versionInfo->contentInfo->id, null, PDO::PARAM_INT)
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return !empty($rows[0]['product_id']) ? (int) $rows[0]['product_id'] : null;
    }

    /**
     * Deletes field data for content id identified by $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds)
    {
        $connection = $this->getConnection();

        $query = $connection->createDeleteQuery();
        $query
            ->deleteFrom($connection->quoteTable('ngsyliusproduct'))
            ->where(
                $query->expr->eq(
                    $connection->quoteColumn('contentobject_id'),
                    $query->bindValue($versionInfo->contentInfo->id, null, PDO::PARAM_INT)
                )
            )
        ;

        $query->prepare()->execute();
    }

    /**
     * Returns the active connection.
     *
     * @throws \RuntimeException if no connection has been set, yet
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ($this->connection === null) {
            throw new RuntimeException('Missing database connection.');
        }

        return $this->connection;
    }
}
