<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\FieldType\SyliusProduct;

use Doctrine\ORM\EntityManagerInterface;
use eZ\Publish\SPI\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class SyliusProductStorage extends GatewayBasedStorage
{
    /**
     * @var \Sylius\Component\Resource\Repository\RepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Sylius\Component\Product\Factory\ProductFactoryInterface
     */
    protected $productFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface
     */
    protected $localeConverter;

    /**
     * Constructor.
     *
     * @param \Sylius\Component\Resource\Repository\RepositoryInterface $productRepository
     * @param \Sylius\Component\Product\Factory\ProductFactoryInterface $productFactory
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     * @param \eZ\Publish\SPI\FieldType\StorageGateway $storageGateway
     */
    public function __construct(
        RepositoryInterface $productRepository,
        ProductFactoryInterface $productFactory,
        EntityManagerInterface $entityManager,
        LocaleConverterInterface $localeConverter,
        StorageGateway $storageGateway
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->entityManager = $entityManager;
        $this->localeConverter = $localeConverter;

        parent::__construct($storageGateway);
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
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $product = $field->value->externalData;
        $productData = $field->value->data;

        if (!$product instanceof ProductInterface) {
            /** @var \Sylius\Component\Core\Model\ProductInterface $product */
            $product = $this->productFactory->createWithVariant();
        }

        if (is_array($productData)) {
            $product->setCode($productData['code']);
            $product->getFirstVariant()->setCode($productData['code']);

            $product->getFirstVariant()->setPrice($productData['price']);

            /** @var \Sylius\Component\Product\Model\ProductTranslationInterface $translation */
            $locale = $this->localeConverter->convertToPOSIX($field->languageCode);
            $translation = $product->translate($locale);

            $product->setName($productData['name']);
            $translation->setName($productData['name']);

            if (isset($productData['description'])) {
                $product->setDescription($productData['description']);
                $translation->setDescription($productData['description']);
            }
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->gateway->storeFieldData($versionInfo, $product->getId());

        return true;
    }

    /**
     * Populates $field value property based on the external data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $productId = $this->gateway->getFieldData($versionInfo);

        $product = null;
        if (!empty($productId)) {
            $product = $this->productRepository->find($productId);
        }

        if ($product instanceof ProductInterface) {
            $product->setCurrentLocale(
                $this->localeConverter->convertToPOSIX($field->languageCode)
            );
        }

        $field->value->externalData = $product;
    }

    /**
     * Deletes field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds Array of field IDs
     * @param array $context
     *
     * @return bool
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        $this->gateway->deleteFieldData($versionInfo, $fieldIds);

        return true;
    }

    /**
     * Checks if field type has external data to deal with.
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * Get index data for external data for search backend.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return array();
    }
}
