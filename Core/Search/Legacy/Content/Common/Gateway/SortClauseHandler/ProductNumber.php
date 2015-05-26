<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;

use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use Netgen\Bundle\EzSyliusBundle\API\Repository\Values\Content\Query\SortClause\ProductNumber as APIProductNumber;

class ProductNumber extends SortClauseHandler
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function accept( SortClause $sortClause )
    {
        return $sortClause instanceof APIProductNumber;
    }

    /**
     * Applies joins to the query
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     */
    public function applyJoin( SelectQuery $query, SortClause $sortClause, $number )
    {
        $table = $this->getSortTableName( $number );
        $externalTable = $this->getSortTableName( $number, "sylius_product_variant" );

        $query
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "ezcontentobject_attribute" ),
                    $this->dbHandler->quoteIdentifier( $table )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "contentobject_id", $table ),
                        $this->dbHandler->quoteColumn( "id", "ezcontentobject" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "version", $table ),
                        $this->dbHandler->quoteColumn( "current_version", "ezcontentobject" )
                    ),
                    $query->expr->gt(
                        $query->expr->bitAnd(
                            $query->expr->bitAnd( $this->dbHandler->quoteColumn( "language_id", $table ), ~1 ),
                            $this->dbHandler->quoteColumn( "initial_language_id", "ezcontentobject" )
                        ),
                        0
                    )
                )
            )
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "sylius_product_variant" ),
                    $this->dbHandler->quoteIdentifier( $externalTable )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "product_id", $externalTable ),
                        $this->dbHandler->quoteColumn( "data_int", $table )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "is_master", $externalTable ),
                        $query->bindValue( 1, null, \PDO::PARAM_INT )
                    )
                )
            );
    }

    /**
     * Apply selects to the query
     *
     * Returns the name of the (aliased) column, which information should be
     * used for sorting.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return string
     */
    public function applySelect( SelectQuery $query, SortClause $sortClause, $number )
    {
        $query->select(
            $query->alias(
                $this->dbHandler->quoteColumn(
                    "sku",
                    $this->getSortTableName( $number, "sylius_product_variant" )
                ),
                $column = $this->getSortColumnName( $number )
            )
        );

        return $column;
    }
}
