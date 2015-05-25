<?php

namespace Netgen\Bundle\EzSyliusBundle\Routing;

use Doctrine\ORM\EntityManagerInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Sylius\Bundle\CoreBundle\Routing\SyliusAwareGenerator as BaseSyliusAwareGenerator;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Sylius\Component\Core\Model\Product;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerInterface;

class SyliusAwareGenerator extends BaseSyliusAwareGenerator
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $mainRouter;

    /**
     * Defines if URL aliases will be generated instead of product slugs
     *
     * @var bool
     */
    protected $generateUrlAliases = false;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\Routing\RouterInterface $mainRouter
     * @param \Symfony\Cmf\Component\Routing\RouteProviderInterface $provider
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $mainRouter,
        RouteProviderInterface $provider,
        LoggerInterface $logger = null
    )
    {
        parent::__construct( $provider, $logger );

        $this->entityManager = $entityManager;
        $this->mainRouter = $mainRouter;
    }

    /**
     * Defines if URL aliases will be generated instead of product slugs
     *
     * @param bool $generateUrlAliases
     */
    public function setGenerateUrlAliases( $generateUrlAliases )
    {
        $this->generateUrlAliases = $generateUrlAliases;
    }

    /**
     * {@inheritDoc}
     */
    public function generate( $name, $parameters = array(), $absolute = false )
    {
        if ( $name instanceof Product )
        {
            if ( $this->generateUrlAliases )
            {
                $syliusProductEntity = $this->entityManager
                    ->getRepository( 'NetgenEzSyliusBundle:SyliusProduct' )
                    ->findBy(
                        array(
                            'productId' => $name->getId()
                        )
                    );

                if ( !empty( $syliusProductEntity ) )
                {
                    try
                    {
                        return $this->mainRouter->generate(
                            'ez_urlalias',
                            $parameters + array(
                                'contentId' => $syliusProductEntity[0]->getContentId()
                            ),
                            $absolute
                        );
                    }
                    catch ( NotFoundException $e )
                    {
                        // Do nothing
                    }
                }
            }
        }

        return parent::generate( $name, $parameters, $absolute );
    }
}
