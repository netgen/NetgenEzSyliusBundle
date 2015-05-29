<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use Doctrine\ORM\EntityManagerInterface;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Core\Model\ProductTranslation;
use eZ\Publish\Core\FieldType\GatewayBasedStorage;

class SyliusProductStorage extends GatewayBasedStorage
{
    /**
     * @var \Sylius\Component\Resource\Repository\RepositoryInterface
     */
    protected $repository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $manager;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \Sylius\Component\Resource\Repository\RepositoryInterface
     */
    protected $taxRepository;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface
     */
    protected $localeConverter;

    /**
     * Constructor
     *
     * @param \Sylius\Component\Resource\Repository\RepositoryInterface $syliusProductRepository
     * @param \Doctrine\ORM\EntityManagerInterface $syliusManager
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \Sylius\Component\Resource\Repository\RepositoryInterface $taxRepository
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     */
    public function __construct(
        RepositoryInterface $syliusProductRepository,
        EntityManagerInterface $syliusManager,
        ContentService $contentService,
        RepositoryInterface $taxRepository,
        LocaleConverterInterface $localeConverter
    )
    {
        $this->repository = $syliusProductRepository;
        $this->manager = $syliusManager;
        $this->contentService = $contentService;
        $this->taxRepository = $taxRepository;
        $this->localeConverter = $localeConverter;
    }

    /**
     * Stores value for $field in an external data source.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return true Indicating internal value data has changed
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /** @var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );

        if ( $field->value->externalData instanceof ProductInterface )
        {
            $product = $field->value->externalData;

            if ( $gateway->checkFieldData( $versionInfo, $product->getId() ) )
            {
                $this->manager->persist( $product );
                $this->manager->flush();

                $gateway->storeFieldData( $versionInfo, $product->getId() );

                $field->value->data = $product->getId();

                return true;
            }

            /** @var \Sylius\Component\Core\Model\Product $copiedProduct */
            $copiedProduct = $this->repository->createNew();

            $translations = $product->getTranslations();
            foreach ( $translations as $translation )
            {
                /** @var \Sylius\Component\Core\Model\ProductTranslation $clonedTranslation */
                $clonedTranslation = clone $translation;
                $clonedTranslation->setTranslatable( $copiedProduct );

                $clonedTranslation->setSlug( $copiedProduct->getName() . '-' . $field->id );
                $copiedProduct->addTranslation( $clonedTranslation );
            }

            $copiedProduct->setPrice( (int)$product->getPrice() );
            /** @var \Sylius\Component\Core\Model\ProductVariant $copiedProductMasterVariant */
            $copiedProductMasterVariant = $copiedProduct->getMasterVariant();
            $copiedProductMasterVariant->setWeight( $product->getMasterVariant()->getWeight() )
                ->setWidth( $product->getMasterVariant()->getWidth() )
                ->setHeight( $product->getMasterVariant()->getHeight() )
                ->setDepth( $product->getMasterVariant()->getDepth() );

            $this->manager->persist( $copiedProduct );
            $this->manager->flush();

            $gateway->storeFieldData( $versionInfo, $copiedProduct->getId() );

            $field->value->data = $copiedProduct->getId();

            return true;
        }
        else if ( is_array( $field->value->externalData ) )
        {
            $createArray = $field->value->externalData;

            $name = $createArray[ 'name' ];
            $price = $createArray[ 'price' ];
            $price *= 100; // sylius feature
            $desc = $createArray[ 'description' ];
            $available_on = $createArray[ 'available_on' ];
            $weight = $createArray[ 'weight' ];
            $height = $createArray[ 'height' ];
            $width = $createArray[ 'width' ];
            $depth = $createArray[ 'depth' ];
            $sku = $createArray[ 'sku' ];
            $tax_category = $createArray[ 'tax_category' ];

            $POSIXLocale = $this->localeConverter->convertToPOSIX( $field->languageCode );

            $product = $this->repository->createNew();

            $translation = new ProductTranslation();
            $translation->setLocale( $POSIXLocale );

            if ( !$product->hasTranslation( $translation ) )
            {
                $product->addTranslation( $translation );
            }

            /** @var \Sylius\Component\Core\Model\Product $product */
            $product
                ->setCurrentLocale( $POSIXLocale )
                ->setName( $name )
                ->setDescription( $desc )
                ->setPrice( (int)$price );

            if ( $available_on )
            {
                if ( !$available_on instanceof \DateTime )
                {
                    $available_on = new \DateTime( $available_on );
                }

                $product->setAvailableOn( $available_on );
            }

            // set tax category
            if ( $tax_category != '0' && !empty( $tax_category ) )
            {
                /** @var \Sylius\Component\Taxation\Model\TaxCategoryInterface $tax_category */
                $tax_category = $this->taxRepository->findOneBy( array( 'name' => $tax_category ) );
                $product->setTaxCategory( $tax_category );
            }

            // set additional info
            /** @var \Sylius\Component\Core\Model\ProductVariant $master_variant */
            $master_variant = $product->getMasterVariant();
            $master_variant->setWeight( $weight )
                ->setHeight( $height )
                ->setWidth( $width )
                ->setDepth( $depth )
                ->setSku( $sku );

            $this->manager->persist( $product );
            $this->manager->flush();

            $gateway->storeFieldData( $versionInfo, $product->getId() );

            // fetch product again to get id
            $productId = $product->getId();
            $field->value->data = $productId;

            return true;
        }

        return false;
    }

    /**
     * Populates $field value property based on the external data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /** @var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );

        $productId = $gateway->getFieldData( $versionInfo );

        $product = null;
        if ( !empty( $productId ) )
        {
            $product = $this->repository->find( $productId );
        }

        if ( $product )
        {
            $product->setCurrentLocale(
                $this->localeConverter->convertToPOSIX( $field->languageCode )
            );
        }

        $field->value->externalData = $product;
    }

    /**
     * Deletes field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds Array of field IDs
     * @param array $context
     *
     * @return boolean
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        $fields = $this->contentService->loadContentByVersionInfo( $versionInfo )->getFields();

        foreach ( $fields as $field )
        {
            if ( in_array( $field->id, $fieldIds ) )
            {
                /**@var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value */
                $value = $field->value;
                $syliusId = $value->product->getId();

                if ( !empty ( $syliusId ) )
                {
                    $product = $this->repository->find( $syliusId );

                    $this->manager->remove( $product );
                    $this->manager->flush();
                }
            }
        }

        /** @var \Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct\SyliusProductStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );

        $gateway->deleteFieldData( $versionInfo, $fieldIds );
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * Get index data for external data for search backend
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        return false;
    }

    /**
     * This method is used exclusively by Legacy Storage to copy external data of existing field in main language to
     * the untranslatable field not passed in create or update struct, but created implicitly in storage layer.
     *
     * By default the method falls back to the {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     * External storages implement this method as needed.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @param array $context
     *
     * @return null|boolean Same as {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     */
    public function copyLegacyField( VersionInfo $versionInfo, Field $field, Field $originalField, array $context )
    {
        return $this->storeFieldData( $versionInfo, $field, $context );
    }
}
