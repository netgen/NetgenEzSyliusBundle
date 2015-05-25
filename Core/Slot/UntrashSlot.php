<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Slot;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class UntrashSlot extends BaseSlot
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Sylius\Component\Resource\Repository\RepositoryInterface
     */
    protected $syliusRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $productEntityManager;

    /**
     * Constructor
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Sylius\Component\Resource\Repository\RepositoryInterface $syliusRepository
     * @param \Doctrine\ORM\EntityManagerInterface $productEntityManager
     */
    public function __construct(
        Repository $repository,
        RepositoryInterface $syliusRepository,
        EntityManagerInterface $productEntityManager
    )
    {
        $this->repository = $repository;
        $this->syliusRepository = $syliusRepository;
        $this->productEntityManager = $productEntityManager;
    }

    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof RecoverSignal )
        {
            return;
        }

        $contentService = $this->repository->getContentService();
        $trashService = $this->repository->getTrashService();

        $trashedItem = $trashService->loadTrashItem( $signal->trashItemId );

        $contentId = $trashedItem->contentId;
        $content = $contentService->loadContent( $contentId );

        $syliusId = $content->getFieldValue( 'sylius_product' )->syliusId;

        if ( !empty( $syliusId ) )
        {
            /** @var \Sylius\Component\Core\Model\Product $product */
            $product = $this->syliusRepository->findForDetailsPage( $syliusId ); // to get deleted product

            if ( $product )
            {
                $product->setDeletedAt( null );
                $product->getMasterVariant()->setDeletedAt( null );
                $this->productEntityManager->persist( $product );
                $this->productEntityManager->flush();
            }
        }
    }
}
