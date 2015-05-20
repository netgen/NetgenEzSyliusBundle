<?php

class eZSyliusFunctionCollection
{
    protected $tax_categories;

    /**
     * Fetches list of tax category objects from sylius database
     *
     * @static
     * @return array
     */
    static public function fetchTaxCategories( )
    {
        $serviceContainer = ezpKernel::instance()->getServiceContainer();
        $taxCategoryRepository = $serviceContainer->get( 'sylius.repository.tax_category' );

        /** @var \Sylius\Component\Taxation\Model\TaxCategoryInterface[] $taxCategories */
        $taxCategories = $taxCategoryRepository->findAll();

        $taxNames = array();
        foreach ( $taxCategories as $taxCategory )
        {
            array_push( $taxNames, $taxCategory->getName() );
        }

        $result = $taxNames;

        if ( is_array( $result ) && !empty( $result ) )
        {
            return array( 'result' => $result );
        }

        return array( 'result' => false );
    }

    /**
     * Fetches list of tax category objects from sylius database
     *
     * @static
     * @return array
     */
    static public function fetchSyliusProducts( )
    {
        $serviceContainer = ezpKernel::instance()->getServiceContainer();
        $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );

        $products = $syliusRepository->findAll();

        $products_array = array();

        /** @var \Sylius\Component\Core\Model\Product $product */
        foreach ( $products as $product )
        {
            $products_array[$product->getId()] = $product->getName();
        }

        $result = $products_array;

        if ( is_array( $products_array ) && !empty( $result ) )
        {
            return array( 'result' => $result );
        }

        return array( 'result' => false );
    }
}
