<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;
use PDO;

class LegacyStorage extends Gateway
{
    /**
     * Connection.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     */
    public function __construct(DatabaseHandler $dbHandler)
    {
          $this->dbHandler = $dbHandler;
    }

    /**
     * Stores the data in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param int $productId
     */
    public function storeFieldData(VersionInfo $versionInfo, $productId)
    {
        $contentId = $versionInfo->contentInfo->id;

        $selectQuery = $this->dbHandler->createSelectQuery();
        $selectQuery
            ->selectDistinct($this->dbHandler->quoteColumn('contentobject_id', 'ngsyliusproduct'))
            ->from($this->dbHandler->quoteTable('ngsyliusproduct'))
            ->where(
                $selectQuery->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ngsyliusproduct'),
                    $selectQuery->bindValue($contentId, null, PDO::PARAM_INT)
                )
            );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $query = $this->dbHandler->createUpdateQuery();
            $query
                ->update('ngsyliusproduct')
                ->set(
                    $this->dbHandler->quoteColumn('product_id'),
                    $query->bindValue($productId, null, PDO::PARAM_INT)
                )
                ->where(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentobject_id', 'ngsyliusproduct'),
                        $query->bindValue($contentId, null, PDO::PARAM_INT)
                    )
                )
            ;
        } else {
            $query = $this->dbHandler->createInsertQuery();
            $query
                ->insertInto($this->dbHandler->quoteTable('ngsyliusproduct'))
                ->set(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId, null, PDO::PARAM_INT)
                )->set(
                    $this->dbHandler->quoteColumn('product_id'),
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
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->selectDistinct($this->dbHandler->quoteColumn('product_id', 'ngsyliusproduct'))
            ->from($this->dbHandler->quoteTable('ngsyliusproduct'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ngsyliusproduct'),
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
        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom($this->dbHandler->quoteTable('ngsyliusproduct'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $query->bindValue($versionInfo->contentInfo->id, null, PDO::PARAM_INT)
                )
            )
        ;

        $query->prepare()->execute();
    }
}
