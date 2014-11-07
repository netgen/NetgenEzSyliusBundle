<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseHandler;
use eZ\Publish\SPI\Persistence\Content\Type;
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
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $fieldTarget */
        $fieldTarget = $sortClause->targetData;
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
                $query->expr->eq(
                        $this->dbHandler->quoteColumn( "product_id", $externalTable ),
                        $this->dbHandler->quoteColumn( "data_int", $table )
                )
            )
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "ezcontentclass_attribute" ),
                    $this->dbHandler->quoteIdentifier( "cc_attr_$number" )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "contentclassattribute_id", $table ),
                        $this->dbHandler->quoteColumn( "id", "cc_attr_$number" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "identifier", "cc_attr_$number" ),
                        $query->bindValue( $fieldTarget->fieldIdentifier )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "version", "cc_attr_$number" ),
                        $query->bindValue( Type::STATUS_DEFINED, null, \PDO::PARAM_INT )
                    )
                )
            )
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "ezcontentclass" ),
                    $this->dbHandler->quoteIdentifier( "cc_$number" )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "contentclass_id", "cc_attr_$number" ),
                        $this->dbHandler->quoteColumn( "id", "cc_$number" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "identifier", "cc_$number" ),
                        $query->bindValue( $fieldTarget->typeIdentifier )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "version", "cc_$number" ),
                        $query->bindValue( Type::STATUS_DEFINED, null, \PDO::PARAM_INT )
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
        //$columns = parent::applySelect( $query, $sortClause, $number );

        return "sku";
    }
}