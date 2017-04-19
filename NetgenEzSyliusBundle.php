<?php

namespace Netgen\Bundle\EzSyliusBundle;

use Netgen\Bundle\EzSyliusBundle\DependencyInjection\CompilerPass\LegacyCachePurgerPass;
use Netgen\Bundle\EzSyliusBundle\DependencyInjection\CompilerPass\SecurityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenEzSyliusBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SecurityPass());
        $container->addCompilerPass(new LegacyCachePurgerPass());
    }
}
