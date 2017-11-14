<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway;
use PDO;

class DoctrineStorage extends Gateway
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
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
        $contentId = $versionInfo->contentInfo->id;

        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery->select('DISTINCT contentobject_id')
            ->from('ngsyliusproduct')
            ->where(
                $selectQuery->expr()->eq('contentobject_id', ':contentobject_id')
            )
            ->setParameter('contentobject_id', $contentId, Type::INTEGER);

        $statement = $selectQuery->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->update('ngsyliusproduct')
                ->set('product_id', ':product_id')
                ->where(
                    $query->expr()->eq('contentobject_id', ':contentobject_id')
                )
                ->setParameter('product_id', $productId, Type::INTEGER)
                ->setParameter('contentobject_id', $contentId, Type::INTEGER)
            ;
        } else {
            $query = $this->connection->createQueryBuilder();
            $query
                ->insert('ngsyliusproduct')
                ->values(
                    array(
                        'product_id' => ':product_id',
                        'contentobject_id' => ':contentobject_id',
                    )
                )
                ->setParameter('product_id', $productId, Type::INTEGER)
                ->setParameter('contentobject_id', $contentId, Type::INTEGER)
            ;
        }

        $query->execute();
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
        $query = $this->connection->createQueryBuilder();
        $query->select('product_id')
            ->from('ngsyliusproduct')
            ->where(
                $query->expr()->eq('contentobject_id', ':contentobject_id')
            )
            ->setParameter('contentobject_id', $versionInfo->contentInfo->id, Type::INTEGER);

        $statement = $query->execute();

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
        $query = $this->connection->createQueryBuilder();
        $query->delete('ngsyliusproduct')
            ->where(
                $query->expr()->eq('contentobject_id', ':contentobject_id')
            )
            ->setParameter('contentobject_id', $versionInfo->contentInfo->id, Type::INTEGER);

        $query->execute();
    }
}
