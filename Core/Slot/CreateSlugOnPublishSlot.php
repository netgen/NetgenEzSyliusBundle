<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Slot;

use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManager;
use eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator;

class CreateSlugOnPublishSlot extends BaseSlot
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    private $syliusRepository;

    private $syliusManager;

    /** @var  array $contentTypes */
    private $contentTypes;

    /** @var  array $fieldTypeMappings */
    private $fieldIdentifierMappings;

    /** @var \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator  */
    private $cacheClearer;

    public function __construct( Repository $repository,
                                 RepositoryInterface $syliusRepository,
                                 EntityManager $syliusManager,
                                 $contentTypes,
                                 $fieldIdentifierMappings,
                                 CacheServiceDecorator $cacheClearer)
    {
        $this->repository = $repository;
        $this->syliusRepository = $syliusRepository;
        $this->syliusManager = $syliusManager;
        $this->contentTypes = $contentTypes;
        $this->fieldIdentifierMappings = $fieldIdentifierMappings;
        $this->cacheClearer = $cacheClearer;
    }

    public function receive( Signal $signal )
    {
        if ( !$signal instanceof PublishVersionSignal )
        {
            return;
        }

        // Load content
        $content = $this->repository->getContentService()->loadContent( $signal->contentId, null, $signal->versionNo );

        $contentInfo = $this->repository->getContentService()->loadContentInfo( $signal->contentId );
        $contentTypeId = $contentInfo->contentTypeId;
        $contentTypeIdentifier = $this->repository->getContentTypeService()->loadContentType($contentTypeId)->identifier;

        if (in_array($contentTypeIdentifier, $this->contentTypes)) {
            $location = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);

            $locationURLAlias = $this->repository->getURLAliasService()->reverseLookup($location);
            $urlAlias = $locationURLAlias->path;
            $urlAlias = ltrim($urlAlias, '/');

            // load sylius product from sylius id in content field type and update it's slug
            $sylius_id = $content->getFieldValue('sylius_product')->syliusId;

            /** @var \Sylius\Component\Core\Model\Product $product */
            $product = $this->syliusRepository->find($sylius_id);
            $product->setSlug($urlAlias);
            $content->getFieldValue('sylius_product')->slug = $product->getSlug();

            // if name and description are empty, we will copy them from eZ content
            $contentTypeId = $contentInfo->contentTypeId;
            $contentTypeIdentifier = $this->repository->getContentTypeService()->loadContentType($contentTypeId)->identifier;

            if (array_key_exists($contentTypeIdentifier, $this->fieldIdentifierMappings))
            {
                $mapping = $this->fieldIdentifierMappings[$contentTypeIdentifier];

                if (!$product->getName() && array_key_exists('name', $mapping ))
                {
                    $contentName = $content->getFieldValue($mapping['name']);
                    $product->setName( $contentName );
                }
                if (!$product->getDescription() && array_key_exists('description', $mapping ))
                {
                    $contentDesc = $content->getFieldValue($mapping['description']);
                    $product->setDescription( $contentDesc );
                }
            }

            $this->syliusManager->persist($product);
            $this->syliusManager->flush();
        }
    }

}