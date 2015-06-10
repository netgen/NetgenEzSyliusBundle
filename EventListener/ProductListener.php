<?php

namespace Netgen\Bundle\EzSyliusBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator;

class ProductListener
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator
     */
    protected $cache;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator $cacheServiceDecorator
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct( CacheServiceDecorator $cacheServiceDecorator, EntityManagerInterface $entityManager )
    {
        $this->cache = $cacheServiceDecorator;
        $this->entityManager = $entityManager;
    }

    /**
     * Executes on Sylius product update
     *
     * @param \Symfony\Component\EventDispatcher\GenericEvent $event
     */
    public function onProductUpdate( GenericEvent $event )
    {
        $subject = $event->getSubject();

        $productId = $subject->getId();

        $syliusProductEntity = $this->entityManager
            ->getRepository( 'NetgenEzSyliusBundle:SyliusProduct' )
            ->findBy(
                array(
                    'productId' => $productId
                )
            );

        if ( !empty( $syliusProductEntity ) )
        {
            $this->cache->clear( 'content', $syliusProductEntity[0]->getContentId() );
        }
    }
}
