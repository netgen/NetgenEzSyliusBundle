<?php

class SyliusProductType extends eZDataType
{
    const DATA_TYPE_STRING = 'syliusproduct';

    /*!
     Initializes with a keyword id and a description.
    */
    public function __construct()
    {
        parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'extension/ngsyliusproduct/datatypes', 'SyliusProduct' ),
            array('serialize_supported' => true) );
        $this->IntegerValidator = new eZIntegerValidator();
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        /*$serviceContainer = ezpKernel::instance()->getServiceContainer();
        $syliusRepository = $serviceContainer->get('sylius.repository.product');
        $syliusManager = $serviceContainer->get('sylius.manager.product');

        /** @var \Sylius\Component\Core\Model\Product $product */
        /*$product = $syliusRepository->find(126);
        $product->setVariants($product->getMasterVariant());
        die(var_dump($product->getAvailableVariants()));
        if($product) {
        }
        die();*/
    }

    /**
     * Fetches the http post var keyword input and stores it in the data instance
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        //if existing product is set
        /* removed because not needed by current use-case
        if ( $http->hasPostVariable($base . "_data_product_" . $contentObjectAttribute->attribute("id")))
        {
            $product_id = $http->postVariable($base . "_data_product_" . $contentObjectAttribute->attribute("id"));

            //fetch product from sylius database
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            $syliusRepository = $serviceContainer->get('sylius.repository.product');
            /** @var \Sylius\Component\Core\Model\Product $product */
            /*$product = $syliusRepository->find($product_id);

            $sylius_product = new SyliusProduct();
            $sylius_product->setSyliusId($product_id);
            $sylius_product->setName( $product->getName() );
            $sylius_product->setAvailableOn( $product->getAvailableOn() );
            $sylius_product->setDescription( $product->getDescription() );
            if ($product->getTaxCategory())
                $sylius_product->setTaxCategory( $product->getTaxCategory()->getName() );
            $sylius_product->setWeight( $product->getMasterVariant()->getWeight() );
            $sylius_product->setHeight( $product->getMasterVariant()->getHeight() );
            $sylius_product->setWidth( $product->getMasterVariant()->getWidth() );
            $sylius_product->setSku( $product->getMasterVariant()->getSku() );

            $contentObjectAttribute->setContent($sylius_product);

            return true;
        }*/

        $sylius_product = new SyliusProduct();

        if ( $http->hasPostVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" )) )
        {
            $dataPrice = $http->postVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) );
            $dataPrice = trim( $dataPrice ) != '' ? $dataPrice : null;
            $dataPrice = str_replace(" ", "", $dataPrice);

            $sylius_product->createFromStrings( $dataPrice );
            $contentObjectAttribute->setContent($sylius_product);
        }
        if ( $http->hasPostVariable( $base . "_data_sylius_id_" . $contentObjectAttribute->attribute( "id" )) )
        {
            $dataId = $http->postVariable( $base . "_data_sylius_id_" . $contentObjectAttribute->attribute( "id" ) );

            $sylius_product->setSyliusId($dataId);
            $contentObjectAttribute->setContent($sylius_product);
        }

        return true;
    }

    /**
     * Stores the object attribute
     *
     * @param eZContentObjectAttribute $attribute
     */
    function storeObjectAttribute( $attribute )
    {
        $sylius_product = $attribute->content();

        if ( $sylius_product instanceof SyliusProduct )
        {
            $sylius_product->store( $attribute );
        }
    }

    /**
     * Delete stored object attribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $version
     */
    function deleteStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
        // We have to delete product from sylius database
        $syliusId = $contentObjectAttribute->content()->sylius_id();

        if ( !empty($syliusId) && !$version )
        {
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            $syliusRepository = $serviceContainer->get('sylius.repository.product');
            $syliusManager = $serviceContainer->get('sylius.manager.product');

            $product = $syliusRepository->find($syliusId);
             if($product) {
                 $syliusManager->remove($product);
                 $syliusManager->flush();
             }
        }
    }

    /**
     * The object is being moved to trash, do any necessary changes to the attribute.
     * Set sylius product to deleted
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $version
     */
    function trashStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
        $this->deleteStoredObjectAttribute( $contentObjectAttribute );
    }

    /**
     * Restores $contentObjectAttribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     */
    public function restoreTrashedObjectAttribute( $contentObjectAttribute )
    {
        $syliusId = $contentObjectAttribute->content()->sylius_id();

        if ( !empty($syliusId) )
        {
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            /** @var \Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository $syliusRepository */
            $syliusRepository = $serviceContainer->get('sylius.repository.product');
            $syliusManager = $serviceContainer->get('sylius.manager.product');

            /** @var \Sylius\Component\Core\Model\Product $product */
            $product = $syliusRepository->findForDetailsPage($syliusId); // to get deleted product

            if($product) {
                $product->setDeletedAt(null);
                $product->getMasterVariant()->setDeletedAt(null);
                $syliusManager->persist($product);
                $syliusManager->flush();
            }
        }
    }


    /**
     * Stores additional data on publish and creates sylius product
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param ezContentObject $contentObject
     * @param $publishedNodes
     */
    function onPublish($contentObjectAttribute, $contentObject, $publishedNodes)
    {
        $http = eZHTTPTool::instance();
        $base = "ContentObjectAttribute"; //default base

        if ( $http->hasPostVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" )) )
        {
            $nodeID = $publishedNodes[0]->MainNodeID;
            $node = eZContentObjectTreeNode::fetch($nodeID);
            $url_alias = $node->urlAlias();

            // get ini settings
            $syliusProductINI = eZINI::instance('ngsyliusproduct.ini');


            // if checkbox for ez name checked
            if ($http->hasPostVariable($base . "_data_ez_name_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_ez_name_" . $contentObjectAttribute->attribute("id")) == 'on'
            ) {
                if (in_array($node->classIdentifier(), $syliusProductINI->variable('Mapping', 'MappedClasses'))) {
                    $mappedNameIdentifier = $syliusProductINI->variable($node->classIdentifier(), 'Name');
                    $name = $node->dataMap()[$mappedNameIdentifier]->toString();
                    //$contentObjectAttribute->content()->setName($name);
                }
            } elseif ($http->hasPostVariable($base . "_data_string_" . $contentObjectAttribute->attribute("id"))) {
                $name = $http->postVariable($base . "_data_string_" . $contentObjectAttribute->attribute("id"));
                $name = trim($name) != '' ? $name : null;
            }

            // if checkbox for ez description checked
            if ($http->hasPostVariable($base . "_data_ez_desc_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_ez_desc_" . $contentObjectAttribute->attribute("id")) == 'on'
            ) {
                $mappedDescIdentifier = $syliusProductINI->variable($node->classIdentifier(), 'Description');
                $desc = $node->dataMap()[$mappedDescIdentifier]->toString();
                $desc = strip_tags($desc);
            } elseif ($http->hasPostVariable($base . "_data_desc_" . $contentObjectAttribute->attribute("id"))) {
                $desc = $http->postVariable($base . "_data_desc_" . $contentObjectAttribute->attribute("id"));
            } else
                $desc = 'eZ Product';

            //check for "available on" information
            $availableDate = false;
            if ($http->hasPostVariable($base . "_data_available_d_" . $contentObjectAttribute->attribute("id")) &&
                is_numeric($http->postVariable($base . "_data_available_d_" . $contentObjectAttribute->attribute("id"))) &&
                $http->hasPostVariable($base . "_data_available_m_" . $contentObjectAttribute->attribute("id")) &&
                is_numeric($http->postVariable($base . "_data_available_m_" . $contentObjectAttribute->attribute("id"))) &&
                $http->hasPostVariable($base . "_data_available_y_" . $contentObjectAttribute->attribute("id")) &&
                is_numeric($http->postVariable($base . "_data_available_y_" . $contentObjectAttribute->attribute("id"))) &&
                $http->hasPostVariable($base . "_data_available_h_" . $contentObjectAttribute->attribute("id")) &&
                is_numeric($http->postVariable($base . "_data_available_h_" . $contentObjectAttribute->attribute("id"))) &&
                $http->hasPostVariable($base . "_data_available_min_" . $contentObjectAttribute->attribute("id")) &&
                is_numeric($http->postVariable($base . "_data_available_min_" . $contentObjectAttribute->attribute("id")))
            ) {
                $day = $http->postVariable($base . "_data_available_d_" . $contentObjectAttribute->attribute("id"));
                $month = $http->postVariable($base . "_data_available_m_" . $contentObjectAttribute->attribute("id"));
                $year = $http->postVariable($base . "_data_available_y_" . $contentObjectAttribute->attribute("id"));
                $hour = $http->postVariable($base . "_data_available_h_" . $contentObjectAttribute->attribute("id"));
                $minute = $http->postVariable($base . "_data_available_min_" . $contentObjectAttribute->attribute("id"));

                $availableDate = DateTime::createFromFormat('d-m-Y-H-i', $day . '-' . $month . '-' . $year . '-' . $hour . '-' . $minute);
            }

            // price
            $price = 0;
            if ($http->hasPostVariable($base . "_data_integer_" . $contentObjectAttribute->attribute("id"))) {
                $price = $http->postVariable($base . "_data_integer_" . $contentObjectAttribute->attribute("id"));
            }

            // weight
            $weight = null;
            if ($http->hasPostVariable($base . "_data_weight_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_weight_" . $contentObjectAttribute->attribute("id")) != ""
            ) {
                $weight = $http->postVariable($base . "_data_weight_" . $contentObjectAttribute->attribute("id"));
            }
            // height
            $height = null;
            if ($http->hasPostVariable($base . "_data_height_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_height_" . $contentObjectAttribute->attribute("id")) != ""
            ) {
                $height = $http->postVariable($base . "_data_height_" . $contentObjectAttribute->attribute("id"));
            }
            // width
            $width = null;
            if ($http->hasPostVariable($base . "_data_width_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_width_" . $contentObjectAttribute->attribute("id")) != ""
            ) {
                $width = $http->postVariable($base . "_data_width_" . $contentObjectAttribute->attribute("id"));
            }

            // depth
            $depth = null;
            if ($http->hasPostVariable($base . "_data_depth_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_depth_" . $contentObjectAttribute->attribute("id")) != ""
            ) {
                $depth = $http->postVariable($base . "_data_depth_" . $contentObjectAttribute->attribute("id"));
            }

            // sku
            $sku = null;
            if ($http->hasPostVariable($base . "_data_sku_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_sku_" . $contentObjectAttribute->attribute("id")) != ""
            ) {
                $sku = $http->postVariable($base . "_data_sku_" . $contentObjectAttribute->attribute("id"));
            }

            // tax category
            $tax_category = null;
            if ($http->hasPostVariable($base . "_data_tax_category_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_tax_category_" . $contentObjectAttribute->attribute("id")) != ""
            ) {
                $tax_category_name = $http->postVariable($base . "_data_tax_category_" . $contentObjectAttribute->attribute("id"));
            }

            // let's save sylius product
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            $syliusRepository = $serviceContainer->get('sylius.repository.product');
            $syliusManager = $serviceContainer->get('sylius.manager.product');

            // check if sylius product already exists
            $sylius_id = $contentObjectAttribute->content()->sylius_id();
            if ($sylius_id) {
                $product = $syliusRepository->find($sylius_id);
            } else {
                $product = $syliusRepository->createNew();
            }

            /** @var \Sylius\Component\Core\Model\Product $product */
            $product
                ->setName($name)
                ->setDescription($desc)
                ->setPrice($price)
                ->setSlug($url_alias);

            // set tax category
            if ($tax_category_name != '0') {
                $taxRepository = $serviceContainer->get('sylius.repository.tax_category');
                $tax_category = $taxRepository->findOneBy(array('name' => $tax_category_name));
                $product->setTaxCategory($tax_category);
            }

            if ($availableDate) {
                $product->setAvailableOn($availableDate);
            }

            // set additional data (weight, height, width, sku)
            /** @var \Sylius\Component\Core\Model\ProductVariant $master_variant */
            $master_variant = $product->getMasterVariant();
            $master_variant->setWeight($weight)
                ->setHeight($height)
                ->setWidth($width)
                ->setDepth($depth)
                ->setSku($sku);

            // custom transliterator
            $listener = $serviceContainer->get('sluggable.listener');
            $listener->setTransliterator(array('Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'transliterate'));
            $listener->setUrlizer(array('Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'urlize'));

            $syliusManager->persist($product);
            $syliusManager->flush();

            // fetch product again to get id
            if (!$sylius_id)
            {
                $product = $syliusRepository->findOneBy(array('slug' => $url_alias));
                $productId = $product->getId();
                $contentObjectAttribute->content()->setSyliusId($productId);
            }
            $contentObjectAttribute->store();
        }
        /*elseif ($http->hasPostVariable($base . "_data_unlink_" . $contentObjectAttribute->attribute("id")) &&
                $http->postVariable($base . "_data_unlink_" . $contentObjectAttribute->attribute("id")) == 'on')
        {
           // delete sylius_id
            $oldId = $contentObjectAttribute->content()->sylius_id();
            $contentObjectAttribute->content()->setSyliusId(null);

            $contentObjectAttribute->store($contentObjectAttribute);

            // delete sylius product
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            $syliusRepository = $serviceContainer->get('sylius.repository.product');
            $syliusManager = $serviceContainer->get('sylius.manager.product');

            $product = $syliusRepository->find($oldId);

            if($product) {
                $syliusManager->remove($product);
                $syliusManager->flush();
            }
        }*/
    }

    /**
     * Returns the content
     *
     * @param eZContentObjectAttribute $attribute
     * @return SyliusProduct
     */
    function objectAttributeContent( $attribute )
    {
        $sylius_product = new SyliusProduct();
        $sylius_product->createFromAttribute( $attribute );

        // fill content with sylius information
        $serviceContainer = ezpKernel::instance()->getServiceContainer();
        $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );

        /** @var Sylius\Component\Core\Model\Product $product */
        $product = $syliusRepository->find($attribute->attribute('data_int'));
        if (!$product)
        {
            return $sylius_product;
        }
        $sylius_product->setName($product->getName());
        $sylius_product->setPrice($product->getPrice());
        $sylius_product->setDescription( $product->getDescription() );
        $sylius_product->setAvailableOn( $product->getAvailableOn() );
        if ($product->getTaxCategory())
            $sylius_product->setTaxCategory( $product->getTaxCategory()->getName() );
        $sylius_product->setWeight( $product->getMasterVariant()->getWeight() );
        $sylius_product->setHeight( $product->getMasterVariant()->getHeight() );
        $sylius_product->setWidth( $product->getMasterVariant()->getWidth() );
        $sylius_product->setDepth( $product->getMasterVariant()->getDepth() );
        $sylius_product->setSku( $product->getMasterVariant()->getSku() );

        return $sylius_product;
    }

    /**
     * Validates the input and returns true if the input was valid for this datatype
     *
     * TODO: validate weight, height, width, tax category
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_data_integer_' . $contentObjectAttribute->attribute( 'id' )) )
        {
            // validate name
            $dataName = $http->postVariable( $base . "_data_string_" . $contentObjectAttribute->attribute( "id" ) );
            $useEZ = $http->postVariable( $base."_data_ez_name_". $contentObjectAttribute->attribute( "id" ) );
            if ( !$useEZ && strlen( $dataName ) == 0 )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Name required.' ) );
                return eZInputValidator::STATE_INVALID;
            }

            // validate price
            $dataPrice = $http->postVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) );
            if (intval($dataPrice) < 0) {
                $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Price must be positive.'));
                return eZInputValidator::STATE_INVALID;
            }

            // validate description
            $dataDesc = $http->postVariable( $base . "_data_desc_" . $contentObjectAttribute->attribute( "id" ) );
            $useEZDesc = $http->postVariable( $base."_data_ez_desc_". $contentObjectAttribute->attribute( "id" ) );
            if ( !$useEZDesc && strlen( $dataDesc ) == 0 )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Description required.' ) );
                return eZInputValidator::STATE_INVALID;
            }

            // validate date
            if ( $http->hasPostVariable($base . "_data_available_d_".$contentObjectAttribute->attribute( "id" )) &&
                 $http->hasPostVariable($base . "_data_available_m_".$contentObjectAttribute->attribute( "id" )) &&
                 $http->hasPostVariable($base . "_data_available_y_".$contentObjectAttribute->attribute( "id" )) &&
                 $http->hasPostVariable($base . "_data_available_h_".$contentObjectAttribute->attribute( "id" )) &&
                 $http->hasPostVariable($base . "_data_available_min_".$contentObjectAttribute->attribute( "id" )) )
            {
                $day = $http->postVariable($base . "_data_available_d_".$contentObjectAttribute->attribute( "id" ));
                $month = $http->postVariable($base . "_data_available_m_".$contentObjectAttribute->attribute( "id" ));
                $year = $http->postVariable($base . "_data_available_y_".$contentObjectAttribute->attribute( "id" ));
                $hour = $http->postVariable($base . "_data_available_h_".$contentObjectAttribute->attribute( "id" ));
                $minute = $http->postVariable($base . "_data_available_min_".$contentObjectAttribute->attribute( "id" ));

                if (!checkdate($month, $day, $year))
                {
                    $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Invalid date.'));
                    return eZInputValidator::STATE_INVALID;
                }
                if ($hour < 0 || $hour > 24 || $minute < 0 || $minute > 59)
                {
                    $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Invalid date.'));
                    return eZInputValidator::STATE_INVALID;
                }

                return eZInputValidator::STATE_ACCEPTED;

            }
            else
            {
                $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Input required.'));
                return eZInputValidator::STATE_INVALID;
            }

        }
        else if ( $contentObjectAttribute->validateIsRequired() )
        {
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Returns string representation of a content object attribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return string
     */
    function toString( $contentObjectAttribute )
    {
        $syliusProduct = new SyliusProduct();
        $syliusProduct->createFromAttribute( $contentObjectAttribute  );

        return $syliusProduct->toString();
    }

    /**
     * Creates the content object attribute content from the input string
     * Valid string value is name, description, price, available_on and sylius product id all together
     * separated by '|#'
     * for example "name|#description|#100|#31-12-2014 23:59|#42"
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $string
     * @return bool
     */
    function fromString( $contentObjectAttribute, $string )
    {
        if ( trim( $string ) != '' )
        {
            $itemsArray = explode( '|#', trim( $string ) );
            if ( is_array( $itemsArray ) && !empty( $itemsArray ) && count( $itemsArray ) == 5 )
            {
                $name = $itemsArray[0];
                $description = $itemsArray[1];
                $price = $itemsArray[2];
                $available_on = $itemsArray[3];
                $syliusId = $itemsArray[4];

                $syliusProduct = new SyliusProduct();
                $syliusProduct->createFromStrings( $price, $name, $description, $syliusId, $available_on );
                $contentObjectAttribute->setContent( $syliusProduct );

                return true;
            }
        }
        return false;
    }

    function sortKey( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'sort_key_int' );
    }

    /**
     * Returns true if content object attribute has content
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        $syliusProduct = new SyliusProduct();
        $syliusProduct->createFromAttribute( $contentObjectAttribute );
        $syliusId = $syliusProduct->sylius_id();

        return !empty( $syliusId );
    }

    /**
     * Sets grouped_input to true for edit view of the datatype
     *
     * @return array
     */
    function objectDisplayInformation( $objectAttribute, $mergeInfo = false )
    {
        $info = array( 'edit' => array( 'grouped_input' => true ) );
        return eZDataType::objectDisplayInformation( $objectAttribute, $info );
    }

    function sortKeyType()
    {
        return 'int';
    }
}

eZDataType::register( SyliusProductType::DATA_TYPE_STRING, 'SyliusProductType' );

?>
