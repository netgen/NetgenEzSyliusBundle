<?php

namespace Netgen\EzSyliusBundle\Core\Slot;

use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManager;

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

    public function __construct( Repository $repository,
                                 RepositoryInterface $syliusRepository,
                                 EntityManager $syliusManager,
                                 $contentTypes)
    {
        $this->repository = $repository;
        $this->syliusRepository = $syliusRepository;
        $this->syliusManager = $syliusManager;
        $this->contentTypes = $contentTypes;
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

        if (in_array($contentInfo->contentTypeId, $this->contentTypes)) {
            $location = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);

            $locationURLAliases = $this->repository->getURLAliasService()->reverseLookup($location);

            // load sylius product from sylius id in content field type and update it's slug
            $sylius_id = $content->getFieldValue('sylius_product')->syliusId;
            $product = $this->syliusRepository->find($sylius_id);

            /** @var \Sylius\Component\Core\Model\Product $product */
            $product->setSlug($locationURLAliases->path);

            $this->syliusManager->persist($product);
            $this->syliusManager->flush();
        }
    }

}