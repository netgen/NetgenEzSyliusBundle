<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Slot;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManager;

class UntrashSlot extends BaseSlot
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $ezRepository;

    private $syliusRepository;

    private $syliusManager;

    public function __construct( Repository $repository,
                                 RepositoryInterface $syliusRepository,
                                 EntityManager $syliusManager )
    {
        $this->ezRepository = $repository;
        $this->syliusRepository = $syliusRepository;
        $this->syliusManager = $syliusManager;
    }


    public function receive( Signal $signal )
    {
        if ( !$signal instanceof RecoverSignal )
        {
            return;
        }

        $contentService = $this->ezRepository->getContentService();
        $trashService = $this->ezRepository->getTrashService();

        $trashedItem = $trashService->loadTrashItem( $signal->trashItemId );

        $contentId = $trashedItem->contentId;
        $content = $contentService->loadContent( $contentId );

        $syliusId = $content->getFieldValue('sylius_product')->syliusId;

        if ( !empty($syliusId) )
        {
            /** @var \Sylius\Component\Core\Model\Product $product */
            $product = $this->syliusRepository->findForDetailsPage($syliusId); // to get deleted product

            if($product) {
                $product->setDeletedAt(null);
                $product->getMasterVariant()->setDeletedAt(null);
                $this->syliusManager->persist($product);
                $this->syliusManager->flush();
            }
        }

    }
}