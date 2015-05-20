<?php

use Sylius\Component\Product\Model\Product;
use Sylius\Component\Core\Model\ProductTranslation;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;

class SyliusProductType extends eZDataType
{
    const DATA_TYPE_STRING = 'syliusproduct';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            self::DATA_TYPE_STRING,
            ezpI18n::tr( 'extension/ngsyliusproduct/datatypes', 'Sylius product' ),
            array(
                'serialize_supported' => true
            )
        );

        $this->IntegerValidator = new eZIntegerValidator();
    }

    /**
     * Initializes the object attribute with some data.
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param int $currentVersion
     * @param eZContentObjectAttribute $originalContentObjectAttribute
     */
    public function initializeObjectAttribute( $objectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        $id = $originalContentObjectAttribute->attribute( 'id' );
        if ( $originalContentObjectAttribute->attribute( 'contentobject_id' ) !== $objectAttribute->attribute( 'contentobject_id' ) )
        {
            eZLog::write( 'COPY - writing to data_text' );
            $objectAttribute->setAttribute( 'data_text', 1 );
            $objectAttribute->store();
        }
        else if ( empty( $id ) )
        {
            $publishedContent = eZContentObject::fetchObject(
                eZContentObject::definition(),
                null,
                array(
                    'id' => $objectAttribute->attribute( 'contentobject_id' ),
                    'status' => eZContentObjectVersion::STATUS_PUBLISHED
                )
            );

            if ( $publishedContent instanceof eZContentObject )
            {
                $attributeMainTranslation = eZContentObjectAttribute::fetchObjectList(
                    eZContentObjectAttribute::definition(),
                    null,
                    array(
                        'contentobject_id' => $objectAttribute->attribute( 'contentobject_id' ),
                        'version' => $publishedContent->attribute( 'current_version' ),
                        'contentclassattribute_id' => $objectAttribute->attribute( 'contentclassattribute_id' )
                    ),
                    null,
                    null,
                    true,
                    false,
                    null,
                    null,
                    ' AND language_id - MOD( language_id, 2 ) = ' . (int)$publishedContent->attribute( 'initial_language_id' )
                );

                if ( !empty( $attributeMainTranslation ) )
                {
                    $objectAttribute->setAttribute(
                        'data_int',
                        $attributeMainTranslation[0]->attribute( 'data_int' )
                    );
                }
            }
        }
    }

    /**
     * Fetches the HTTP input for the content object attribute.
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return bool
     */
    public function fetchObjectAttributeHTTPInput( $http, $base, $objectAttribute )
    {
        // if existing product is set
        /* Removed because not needed by current use-case
        if ( $http->hasPostVariable( $base . "_data_product_" . $objectAttribute->attribute( "id" ) ) )
        {
            $productId = $http->postVariable( $base . "_data_product_" . $objectAttribute->attribute( "id" ) );

            //fetch product from sylius database
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );
            /** @var \Sylius\Component\Core\Model\Product $product */
            /*$product = $syliusRepository->find( $productId );

            $syliusProduct = new SyliusProduct();
            $syliusProduct->setSyliusId( $productId );
            $syliusProduct->setName( $product->getName() );
            $syliusProduct->setAvailableOn( $product->getAvailableOn() );
            $syliusProduct->setDescription( $product->getDescription() );
            if ( $product->getTaxCategory() )
            {
                $syliusProduct->setTaxCategory( $product->getTaxCategory()->getName() );
            }
            $syliusProduct->setWeight( $product->getMasterVariant()->getWeight() );
            $syliusProduct->setHeight( $product->getMasterVariant()->getHeight() );
            $syliusProduct->setWidth( $product->getMasterVariant()->getWidth() );
            $syliusProduct->setSku( $product->getMasterVariant()->getSku() );

            $objectAttribute->setContent( $syliusProduct );

            return true;
        }*/
    }

    /**
     * Stores the datatype data to the database which is related to the object attribute.
     *
     * @param eZContentObjectAttribute $objectAttribute
     */
    public function storeObjectAttribute( $objectAttribute )
    {
        $syliusProduct = $objectAttribute->content();

        if ( $syliusProduct instanceof SyliusProduct )
        {
            $objectAttribute->setAttribute( 'data_int', $syliusProduct->syliusId() );
        }
    }

    /**
     * Deletes $objectAttribute datatype data, optionally in version $version.
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param int $version
     */
    public function deleteStoredObjectAttribute( $objectAttribute, $version = null )
    {
        // We have to delete product from sylius database
        /** @var SyliusProduct $syliusProduct */
        $syliusProduct = $objectAttribute->content();
        $syliusId = $syliusProduct->syliusId();

        if ( !empty( $syliusId ) && empty( $version ) )
        {
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );
            $syliusManager = $serviceContainer->get( 'sylius.manager.product' );

            $product = $syliusRepository->find( $syliusId );
            if ( $product )
            {
                $syliusManager->remove( $product );
                $syliusManager->flush();
            }
        }
    }

    /**
     * Do any necessary changes to stored object attribute when moving an object to trash.
     * Set Sylius product to deleted
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param int $version
     */
    public function trashStoredObjectAttribute( $objectAttribute, $version = null )
    {
        $this->deleteStoredObjectAttribute( $objectAttribute );
    }

    /**
     * Restores the content object attribute $objectAttribute from trash
     *
     * @param eZContentObjectAttribute $objectAttribute
     */
    public function restoreTrashedObjectAttribute( $objectAttribute )
    {
        /** @var SyliusProduct $syliusProduct */
        $syliusProduct = $objectAttribute->content();
        $syliusId = $syliusProduct->syliusId();

        if ( !empty( $syliusId ) )
        {
            $serviceContainer = ezpKernel::instance()->getServiceContainer();

            /** @var \Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository $syliusRepository */
            $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );
            $syliusManager = $serviceContainer->get( 'sylius.manager.product' );

            /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter */
            $localeConverter = $serviceContainer->get( 'ezpublish.locale.converter' );

            /** @var \Sylius\Component\Core\Model\Product $product */
            $product = $syliusRepository->findForDetailsPage( $syliusId ); // to get deleted product

            $attributeLocale = $objectAttribute->attribute( 'language_code' );

            if ( $product )
            {
                $product->setCurrentLocale( $localeConverter->convertToPOSIX( $attributeLocale ) );
                $product->setDeletedAt( null );
                $product->getMasterVariant()->setDeletedAt( null );
                $syliusManager->persist( $product );
                $syliusManager->flush();
            }
        }
    }

    /**
     * Stores additional data on publish and creates Sylius product
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param ezContentObject $contentObject
     * @param eZContentObjectTreeNode[] $publishedNodes
     */
    public function onPublish( $contentObjectAttribute, $contentObject, $publishedNodes )
    {
        $http = eZHTTPTool::instance();
        $base = "ContentObjectAttribute"; //default base

        $nodeID = $publishedNodes[0]->MainNodeID;
        $node = eZContentObjectTreeNode::fetch( $nodeID );
        $urlAlias = $node->urlAlias();

        $serviceContainer = ezpKernel::instance()->getServiceContainer();
        /** @var LocaleConverterInterface $localeConverter */
        $localeConverter = $serviceContainer->get( 'ezpublish.locale.converter' );

        if ( $contentObjectAttribute->attribute( 'data_text' ) != 1 )
        {
            if ( $http->hasPostVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) ) )
            {
                // get ini settings
                $syliusProductINI = eZINI::instance( 'ngsyliusproduct.ini' );

                // if checkbox for ez name checked
                if (
                    $http->hasPostVariable( $base . "_data_ez_name_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $syliusProductINI->hasVariable( $node->classIdentifier(), 'Name' )
                )
                {
                    $mappedNameIdentifier = $syliusProductINI->variable( $node->classIdentifier(), 'Name' );
                    $dataMap = $node->dataMap();
                    $name = $dataMap[$mappedNameIdentifier]->content();
                    //$contentObjectAttribute->content()->setName($name);
                }
                else if ( $http->hasPostVariable( $base . "_data_string_" . $contentObjectAttribute->attribute( "id" ) ) )
                {
                    $name = trim( $http->postVariable( $base . "_data_string_" . $contentObjectAttribute->attribute( "id" ) ) );
                }
                else
                {
                    $name = '';
                }

                // if checkbox for ez description checked
                if (
                    $http->hasPostVariable( $base . "_data_ez_desc_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $syliusProductINI->hasVariable( $node->classIdentifier(), 'Description' )
                )
                {
                    $mappedDescIdentifier = $syliusProductINI->variable( $node->classIdentifier(), 'Description' );
                    $dataMap = $node->dataMap();
                    $desc = $dataMap[$mappedDescIdentifier]->content()->attribute( 'output' )->attribute( 'output_text' );
                    $desc = strip_tags( $desc );
                }
                else if ( $http->hasPostVariable( $base . "_data_desc_" . $contentObjectAttribute->attribute( "id" ) ) )
                {
                    $desc = $http->postVariable( $base . "_data_desc_" . $contentObjectAttribute->attribute( "id" ) );
                }
                else
                {
                    $desc = '';
                }

                //check for "available on" information
                $availableDate = false;
                if (
                    $http->hasPostVariable( $base . "_data_available_d_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    is_numeric( $http->postVariable( $base . "_data_available_d_" . $contentObjectAttribute->attribute( "id" ) ) ) &&
                    $http->hasPostVariable( $base . "_data_available_m_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    is_numeric( $http->postVariable( $base . "_data_available_m_" . $contentObjectAttribute->attribute( "id" ) ) ) &&
                    $http->hasPostVariable( $base . "_data_available_y_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    is_numeric( $http->postVariable( $base . "_data_available_y_" . $contentObjectAttribute->attribute( "id" ) ) ) &&
                    $http->hasPostVariable( $base . "_data_available_h_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    is_numeric( $http->postVariable( $base . "_data_available_h_" . $contentObjectAttribute->attribute( "id" ) ) ) &&
                    $http->hasPostVariable( $base . "_data_available_min_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    is_numeric( $http->postVariable( $base . "_data_available_min_" . $contentObjectAttribute->attribute( "id" ) ) )
                )
                {
                    $day = $http->postVariable( $base . "_data_available_d_" . $contentObjectAttribute->attribute( "id" ) );
                    $month = $http->postVariable( $base . "_data_available_m_" . $contentObjectAttribute->attribute( "id" ) );
                    $year = $http->postVariable( $base . "_data_available_y_" . $contentObjectAttribute->attribute( "id" ) );
                    $hour = $http->postVariable( $base . "_data_available_h_" . $contentObjectAttribute->attribute( "id" ) );
                    $minute = $http->postVariable( $base . "_data_available_min_" . $contentObjectAttribute->attribute( "id" ) );

                    $availableDate = DateTime::createFromFormat( 'd-m-Y-H-i', $day . '-' . $month . '-' . $year . '-' . $hour . '-' . $minute );
                }

                // price
                $price = 0;
                if (
                    $http->hasPostVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $http->postVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) ) > 0
                )
                {
                    $price = $http->postVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) );
                    $price = $price * 100; // sylius feature
                }

                // weight
                $weight = null;
                if (
                    $http->hasPostVariable( $base . "_data_weight_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $http->postVariable( $base . "_data_weight_" . $contentObjectAttribute->attribute( "id" ) ) != ""
                )
                {
                    $weight = (float)$http->postVariable( $base . "_data_weight_" . $contentObjectAttribute->attribute( "id" ) );
                }

                // height
                $height = null;
                if (
                    $http->hasPostVariable( $base . "_data_height_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $http->postVariable( $base . "_data_height_" . $contentObjectAttribute->attribute( "id" ) ) != ""
                )
                {
                    $height = (float)$http->postVariable( $base . "_data_height_" . $contentObjectAttribute->attribute( "id" ) );
                }

                // width
                $width = null;
                if (
                    $http->hasPostVariable( $base . "_data_width_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $http->postVariable( $base . "_data_width_" . $contentObjectAttribute->attribute( "id" ) ) != ""
                )
                {
                    $width = (float)$http->postVariable( $base . "_data_width_" . $contentObjectAttribute->attribute( "id" ) );
                }

                // depth
                $depth = null;
                if (
                    $http->hasPostVariable( $base . "_data_depth_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $http->postVariable( $base . "_data_depth_" . $contentObjectAttribute->attribute( "id" ) ) != ""
                )
                {
                    $depth = (float)$http->postVariable( $base . "_data_depth_" . $contentObjectAttribute->attribute( "id" ) );
                }

                // sku
                $sku = null;
                if (
                    $http->hasPostVariable( $base . "_data_sku_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $http->postVariable( $base . "_data_sku_" . $contentObjectAttribute->attribute( "id" ) ) != ""
                )
                {
                    $sku = $http->postVariable( $base . "_data_sku_" . $contentObjectAttribute->attribute( "id" ) );
                }

                // tax category
                $taxCategory = null;
                if (
                    $http->hasPostVariable( $base . "_data_tax_category_" . $contentObjectAttribute->attribute( "id" ) ) &&
                    $http->postVariable( $base . "_data_tax_category_" . $contentObjectAttribute->attribute( "id" ) ) != ""
                )
                {
                    $taxCategoryName = $http->postVariable( $base . "_data_tax_category_" . $contentObjectAttribute->attribute( "id" ) );
                }

                // let's save sylius product
                $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );
                $syliusManager = $serviceContainer->get( 'sylius.manager.product' );

                $attributeLocale = $contentObjectAttribute->attribute( 'language_code' );

                /** @var SyliusProduct $syliusProduct */
                $syliusProduct = $contentObjectAttribute->content();

                // check if sylius product already exists
                $syliusId = $syliusProduct->syliusId();
                if ( $syliusId )
                {
                    /** @var \Sylius\Component\Core\Model\Product $product */
                    $product = $syliusRepository->find( $syliusId );
                }
                else
                {
                    /** @var \Sylius\Component\Core\Model\Product $product */
                    $product = $syliusRepository->createNew();
                    eZLog::write( 'COPY - created new product in onPublish' );
                }

                $translation = new ProductTranslation();
                $translation->setLocale( $localeConverter->convertToPOSIX( $attributeLocale ) );

                if ( !$product->hasTranslation( $translation ) )
                {
                    $product->addTranslation( $translation );
                }

                /** @var \Sylius\Component\Core\Model\Product $product */
                $product->setCurrentLocale( $localeConverter->convertToPOSIX( $attributeLocale ) )
                    ->setName( $name )
                    ->setDescription( $desc )
                    ->setPrice( (int)$price )
                    ->setSlug( $urlAlias );

                // set tax category
                if ( isset( $taxCategoryName ) && $taxCategoryName != '0' )
                {
                    $taxRepository = $serviceContainer->get( 'sylius.repository.tax_category' );

                    /** @var \Sylius\Component\Taxation\Model\TaxCategoryInterface $taxCategory */
                    $taxCategory = $taxRepository->findOneBy( array( 'name' => $taxCategoryName ) );
                    $product->setTaxCategory( $taxCategory );
                }

                if ( $availableDate )
                {
                    $product->setAvailableOn( $availableDate );
                }

                /** @var \Sylius\Component\Core\Model\ProductVariant $masterVariant */
                $masterVariant = $product->getMasterVariant();
                $masterVariant->setWeight( $weight )
                    ->setHeight( $height )
                    ->setWidth( $width )
                    ->setDepth( $depth )
                    ->setSku( $sku );

                // custom transliterator
                $listener = $serviceContainer->get( 'sluggable.listener' );
                $listener->setTransliterator( array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'transliterate' ) );
                $listener->setUrlizer( array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'urlize' ) );

                $syliusManager->persist( $product );
                $syliusManager->flush();

                // fetch product again to get id
                if ( !$syliusId )
                {
                    $syliusProduct->setSyliusId( $product->getId() );
                }

                $contentObjectAttribute->store();
            }
        }
        else
        {
            // ON COPY
            $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );
            $syliusManager = $serviceContainer->get( 'sylius.manager.product' );

            // check if sylius product already exists
            $syliusId = $contentObjectAttribute->attribute( 'data_int' );
            $product = null;
            if ( $syliusId )
            {
                /** @var \Sylius\Component\Core\Model\Product $product */
                $product = $syliusRepository->find( $syliusId );
            }

            if ( $product )
            {
                /** @var \Sylius\Component\Core\Model\Product $copiedProduct */
                $copiedProduct = $syliusRepository->createNew();
                eZLog::write( 'COPY - created new product in onPublish (else)' );

                $translations = $product->getTranslations();
                foreach ( $translations as $translation )
                {
                    $clonedTranslation = clone $translation;
                    $clonedTranslation->setTranslatable( $copiedProduct );
                    $clonedTranslation->setSlug( $urlAlias );

                    $copiedProduct->addTranslation( $clonedTranslation );
                }

                $copiedProduct->setPrice( (int)$product->getPrice() );
                /** @var \Sylius\Component\Core\Model\ProductVariant $copiedProductMasterVariant */
                $copiedProductMasterVariant = $copiedProduct->getMasterVariant();
                $copiedProductMasterVariant->setWeight( $product->getMasterVariant()->getWeight() )
                    ->setWidth( $product->getMasterVariant()->getWidth() )
                    ->setHeight( $product->getMasterVariant()->getHeight() )
                    ->setDepth( $product->getMasterVariant()->getDepth() );

                $listener = $serviceContainer->get( 'sluggable.listener' );
                $listener->setTransliterator( array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'transliterate' ) );
                $listener->setUrlizer( array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'urlize' ) );

                $syliusManager->persist( $copiedProduct );
                $syliusManager->flush();

                /** @var SyliusProduct $syliusProduct */
                $syliusProduct = $contentObjectAttribute->content();
                $syliusProduct->setSyliusId( $copiedProduct->getId() );

                $contentObjectAttribute->setAttribute( 'data_text', 0 );
                $contentObjectAttribute->store();
            }
            else
            {
                eZLog::write( 'COPY - something went wrong, creating new product' );
                $product = $syliusRepository->createNew();
                $product->setName( $contentObject->name() );

                $listener = $serviceContainer->get( 'sluggable.listener' );
                $listener->setTransliterator( array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'transliterate' ) );
                $listener->setUrlizer( array( 'Netgen\Bundle\EzSyliusBundle\Util\Urlizer', 'urlize' ) );

                $syliusManager->persist( $product );
                $syliusManager->flush();

                /** @var SyliusProduct $syliusProduct */
                $syliusProduct = $contentObjectAttribute->content();
                $syliusProduct->setSyliusId( $product->getId() );

                $contentObjectAttribute->setAttribute( 'data_text', 0 );
                $contentObjectAttribute->store();
            }
        }
        // uncomment this if there is need to unlink sylius product from eZ object
        /*else if (
            $http->hasPostVariable( $base . "_data_unlink_" . $contentObjectAttribute->attribute( "id" ) ) &&
            $http->postVariable( $base . "_data_unlink_" . $contentObjectAttribute->attribute( "id" ) ) == 'on'
        )
        {
           // delete sylius id
            $oldId = $contentObjectAttribute->content()->syliusId();
            $contentObjectAttribute->content()->setSyliusId( null );

            $contentObjectAttribute->store( $contentObjectAttribute );

            // delete sylius product
            $serviceContainer = ezpKernel::instance()->getServiceContainer();
            $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );
            $syliusManager = $serviceContainer->get( 'sylius.manager.product' );

            $product = $syliusRepository->find( $oldId );

            if ( $product )
            {
                $syliusManager->remove( $product );
                $syliusManager->flush();
            }
        }*/
    }

    /**
     * Returns the content data for the given content object attribute.
     *
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return SyliusProduct
     */
    public function objectAttributeContent( $objectAttribute )
    {
        $syliusProduct = new SyliusProduct();

        // fill content with sylius information
        $serviceContainer = ezpKernel::instance()->getServiceContainer();
        $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );

        /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter */
        $localeConverter = $serviceContainer->get( 'ezpublish.locale.converter' );

        /** @var \Sylius\Component\Core\Model\Product $product */
        $product = $syliusRepository->findForDetailsPage( $objectAttribute->attribute( 'data_int' ) );

        $attributeLocale = $objectAttribute->attribute( 'language_code' );

        if ( $product )
        {
            $product->setCurrentLocale(
                $localeConverter->convertToPOSIX( $attributeLocale )
            );

            $syliusProduct->createFromSylius( $product );
        }

        return $syliusProduct;
    }

    /**
     * Validates the input for an object attribute and returns a validation
     * state as defined in eZInputValidator.
     *
     * TODO: validate tax category
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return bool
     */
    public function validateObjectAttributeHTTPInput( $http, $base, $objectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_data_integer_' . $objectAttribute->attribute( 'id' ) ) )
        {
            // validate name
            if ( $http->hasPostVariable( $base . '_data_string_' . $objectAttribute->attribute( 'id' ) ) )
            {
                $dataName = $http->postVariable( $base . "_data_string_" . $objectAttribute->attribute( "id" ) );
                $useEZ = $http->postVariable( $base . "_data_ez_name_" . $objectAttribute->attribute( "id" ) );
                if ( !$useEZ && strlen( $dataName ) == 0 )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Name required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

            // validate price
            $dataPrice = $http->postVariable( $base . "_data_integer_" . $objectAttribute->attribute( "id" ) );
            if ( intval( $dataPrice ) < 0 )
            {
                $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Price must be positive or zero.' ) );
                return eZInputValidator::STATE_INVALID;
            }

            // validate description
            if ( $http->hasPostVariable( $base . '_data_desc_' . $objectAttribute->attribute( 'id' ) ) )
            {
                $dataDesc = $http->postVariable( $base . "_data_desc_" . $objectAttribute->attribute( "id" ) );
                $useEZDesc = $http->postVariable( $base . "_data_ez_desc_" . $objectAttribute->attribute( "id" ) );
                if ( !$useEZDesc && strlen( $dataDesc ) == 0 )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Description required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

            // validate date
            if (
                $http->hasPostVariable( $base . "_data_available_d_" . $objectAttribute->attribute( "id" ) ) &&
                $http->hasPostVariable( $base . "_data_available_m_" . $objectAttribute->attribute( "id" ) ) &&
                $http->hasPostVariable( $base . "_data_available_y_" . $objectAttribute->attribute( "id" ) ) &&
                $http->hasPostVariable( $base . "_data_available_h_" . $objectAttribute->attribute( "id" ) ) &&
                $http->hasPostVariable( $base . "_data_available_min_" . $objectAttribute->attribute( "id" ) )
            )
            {
                $day = $http->postVariable( $base . "_data_available_d_" . $objectAttribute->attribute( "id" ) );
                $month = $http->postVariable( $base . "_data_available_m_" . $objectAttribute->attribute( "id" ) );
                $year = $http->postVariable( $base . "_data_available_y_" . $objectAttribute->attribute( "id" ) );
                $hour = $http->postVariable( $base . "_data_available_h_" . $objectAttribute->attribute( "id" ) );
                $minute = $http->postVariable( $base . "_data_available_min_" . $objectAttribute->attribute( "id" ) );

                if ( !checkdate( $month, $day, $year ) )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Invalid date.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
                if ( $hour < 0 || $hour >= 24 || $minute < 0 || $minute > 59 )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Invalid time.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

            // validate weight
            if ( $http->hasPostVariable( $base . '_data_weight_' . $objectAttribute->attribute( 'id' ) ) )
            {
                $dataWeight = $http->postVariable( $base . "_data_weight_" . $objectAttribute->attribute( "id" ) );
                if ( !is_numeric( $dataWeight ) || intval( $dataWeight ) < 0 )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Weight must be positive or zero.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

            // validate height
            if ( $http->hasPostVariable( $base . '_data_height_' . $objectAttribute->attribute( 'id' ) ) )
            {
                $dataHeight = $http->postVariable( $base . "_data_height_" . $objectAttribute->attribute( "id" ) );
                if ( !is_numeric( $dataHeight ) || intval( $dataHeight ) < 0 )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Height must be positive or zero.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

            // validate width
            if ( $http->hasPostVariable( $base . '_data_width_' . $objectAttribute->attribute( 'id' ) ) )
            {
                $dataWidth = $http->postVariable( $base . "_data_width_" . $objectAttribute->attribute( "id" ) );
                if ( !is_numeric( $dataWidth ) || intval( $dataWidth ) < 0 )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Width must be positive or zero.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

            // validate depth
            if ( $http->hasPostVariable( $base . '_data_depth_' . $objectAttribute->attribute( 'id' ) ) )
            {
                $dataDepth = $http->postVariable( $base . "_data_depth_" . $objectAttribute->attribute( "id" ) );
                if ( !is_numeric( $dataDepth ) || intval( $dataDepth ) < 0 )
                {
                    $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Depth must be positive or zero.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }
        }
        else if ( $objectAttribute->validateIsRequired() )
        {
            $objectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Returns string representation of a content object attribute
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @return string
     */
    public function toString( $objectAttribute )
    {
        $syliusProduct = new SyliusProduct();

        $serviceContainer = ezpKernel::instance()->getServiceContainer();
        $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );

        /** @var \Sylius\Component\Core\Model\Product $product */
        $product = $syliusRepository->find( $objectAttribute->attribute( 'data_int' ) );
        if ( $product )
        {
            $syliusProduct->createFromSylius( $product );
        }

        return $syliusProduct->toString();
    }

    /**
     * Creates the content object attribute content from the input string
     * Valid string value is name, description, price, available_on and sylius product id all together
     * separated by '|#'
     * for example "name|#description|#100|#31-12-2014 23:59|#42"
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param string $string
     *
     * @return bool
     */
    public function fromString( $objectAttribute, $string )
    {
        if ( trim( $string ) != '' )
        {
            $itemsArray = explode( '|#', trim( $string ) );
            if ( is_array( $itemsArray ) && !empty( $itemsArray ) && count( $itemsArray ) == 5 )
            {
                $name = $itemsArray[0];
                $description = $itemsArray[1];
                $price = $itemsArray[2];
                $availableOn = $itemsArray[3];
                $syliusId = $itemsArray[4];

                $syliusProduct = new SyliusProduct();
                $syliusProduct->createFromStrings( $price, $name, $description, $syliusId, $availableOn );
                $objectAttribute->setContent( $syliusProduct );

                return true;
            }
        }
        return false;
    }

    /**
     * Returns the sort key for the datatype. This is used for sorting on attribute level.
     *
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return mixed
     */
    public function sortKey( $objectAttribute )
    {
        return $objectAttribute->attribute( 'sort_key_int' );
    }

    /**
     * Returns true if the datatype finds any content in the attribute
     *
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return bool
     */
    public function hasObjectAttributeContent( $objectAttribute )
    {
        /** @var SyliusProduct $syliusProduct */
        $syliusProduct = $objectAttribute->content();
        $syliusId = $syliusProduct->syliusId();

        $serviceContainer = ezpKernel::instance()->getServiceContainer();
        $syliusRepository = $serviceContainer->get( 'sylius.repository.product' );

        /** @var Product $product */
        $product = $syliusRepository->find( $syliusId );

        return !empty( $product );
    }

    /**
     * Will return information on how the datatype should be represented in
     * the various display modes when used by an object.
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param array|bool $mergeInfo
     *
     * @return array
     */
    public function objectDisplayInformation( $objectAttribute, $mergeInfo = false )
    {
        $info = array(
            'edit' => array(
                'grouped_input' => true
            )
        );

        return eZDataType::objectDisplayInformation( $objectAttribute, $info );
    }

    /**
     * Returns the type of the sort key
     *
     * @return string
     */
    public function sortKeyType()
    {
        return 'int';
    }
}

eZDataType::register( SyliusProductType::DATA_TYPE_STRING, 'SyliusProductType' );
