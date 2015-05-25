<?php

namespace Netgen\Bundle\EzSyliusBundle\Core\Slot;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal;
use Netgen\Bundle\EzSyliusBundle\Entity\SyliusProduct;
use Sylius\Component\Core\Model\Product;

class TrashSlot extends BaseSlot
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Sylius\Component\Resource\Repository\RepositoryInterface
     */
    protected $syliusRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $syliusEntityManager;

    /**
     * Constructor
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Sylius\Component\Resource\Repository\RepositoryInterface $syliusRepository
     * @param \Doctrine\ORM\EntityManagerInterface $syliusEntityManager
     */
    public function __construct(
        Repository $repository,
        EntityManagerInterface $entityManager,
        RepositoryInterface $syliusRepository,
        EntityManagerInterface $syliusEntityManager
    )
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->syliusRepository = $syliusRepository;
        $this->syliusEntityManager = $syliusEntityManager;
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

        try
        {
            $location = $this->repository->getLocationService()
                ->loadLocation( $signal->locationId );
        }
        catch ( NotFoundException $e )
        {
            return;
        }

        $syliusProductEntity = $this->entityManager
            ->getRepository( 'NetgenEzSyliusBundle:SyliusProduct' )
            ->find( $location->getContentInfo()->id );

        if ( !$syliusProductEntity instanceof SyliusProduct )
        {
            return;
        }

        $product = $this->syliusRepository->find( $syliusProductEntity->getProductId() );
        if ( !$product instanceof Product )
        {
            return;
        }

        $this->syliusEntityManager->remove( $product );
        $this->syliusEntityManager->flush();
    }
}
