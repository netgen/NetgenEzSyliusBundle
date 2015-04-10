<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Slot;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManager;

class TrashSlot extends BaseSlot
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $ezRepository;

    private $syliusRepository;

    private $syliusManager;

    public function __construct(
        Repository $repository,
        RepositoryInterface $syliusRepository,
        EntityManager $syliusManager
    )
    {
        $this->ezRepository = $repository;
        $this->syliusRepository = $syliusRepository;
        $this->syliusManager = $syliusManager;
    }


    public function receive( Signal $signal )
    {
        if ( !$signal instanceof TrashSignal )
        {
            return;
        }

        $locationId = $signal->locationId;

        $locationService = $this->ezRepository->getLocationService();
        $contentService = $this->ezRepository->getContentService();
        $location = $locationService->loadLocation( $locationId );

        $contentInfo = $location->getContentInfo();

        $content = $contentService->loadContent( $contentInfo->id );

        $syliusId = $content->getFieldValue( 'sylius_product' )->syliusId;

        if ( !empty( $syliusId ) )
        {
            $product = $this->syliusRepository->find( $syliusId );
            if ( $product )
            {
                $this->syliusManager->remove( $product );
                $this->syliusManager->flush();
            }
        }
    }
}
