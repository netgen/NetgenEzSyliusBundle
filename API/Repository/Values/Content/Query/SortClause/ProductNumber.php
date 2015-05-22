<?php

namespace Netgen\Bundle\EzSyliusBundle\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class ProductNumber extends SortClause
{
    /**
     * Constructs a new ProductNumber SortClause
     *
     * @param string $sortDirection
     */
    public function __construct( $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'field', $sortDirection );
    }
}
