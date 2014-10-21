<?php

$FunctionList = array();

$FunctionList['tax_categories'] = array(
    'name' => 'tax_categories',
    'operation_types' => array( 'read' ),
    'call_method'     => array(
        'class'  => 'eZSyliusFunctionCollection',
        'method' => 'fetchTaxCategories'
    ),
    'parameter_type'  => 'standard',
    'parameters'      => array()
);

$FunctionList['products'] = array(
    'name' => 'products',
    'operation_types' => array( 'read' ),
    'call_method'     => array(
        'class'  => 'eZSyliusFunctionCollection',
        'method' => 'fetchSyliusProducts'
    ),
    'parameter_type'  => 'standard',
    'parameters'      => array()
);

?>