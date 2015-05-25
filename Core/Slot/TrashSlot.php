<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Slot;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class TrashSlot extends BaseSlot
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
        if ( !$signal instanceof TrashSignal )
        {
            return;
        }

        $locationId = $signal->locationId;

        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();
        $location = $locationService->loadLocation( $locationId );

        $contentInfo = $location->getContentInfo();

        $content = $contentService->loadContent( $contentInfo->id );

        $syliusId = $content->getFieldValue( 'sylius_product' )->syliusId;

        if ( !empty( $syliusId ) )
        {
            $product = $this->syliusRepository->find( $syliusId );
            if ( $product )
            {
                $this->productEntityManager->remove( $product );
                $this->productEntityManager->flush();
            }
        }
    }
}
