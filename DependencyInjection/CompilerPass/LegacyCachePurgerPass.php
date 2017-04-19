<?php

namespace Netgen\Bundle\EzSyliusBundle\DependencyInjection\CompilerPass;

use Netgen\Bundle\EzSyliusBundle\Cache\LegacyCachePurger;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LegacyCachePurgerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish_legacy.legacy_cache_purger')) {
            return;
        }

        // Overrides Legacy Bridge cache purger to set "quiet" flag to eZCLI,
        // since it crashes Sylius installation procedure.

        $container
            ->findDefinition('ezpublish_legacy.legacy_cache_purger')
            ->setClass(LegacyCachePurger::class);
    }
}
