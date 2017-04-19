<?php

namespace Netgen\Bundle\EzSyliusBundle\DependencyInjection\CompilerPass;

use Netgen\Bundle\EzSyliusBundle\Authentication\AuthenticationSuccessHandler;
use Netgen\Bundle\EzSyliusBundle\Authentication\DaoAuthenticationProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SecurityPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('security.authentication.success_handler')) {
            $container
                ->findDefinition('security.authentication.success_handler')
                ->setClass(AuthenticationSuccessHandler::class);
        }

        if ($container->has('security.authentication.provider.dao')) {
            $container
                ->findDefinition('security.authentication.provider.dao')
                ->setClass(DaoAuthenticationProvider::class);
        }

        if ($container->has('sylius.authentication.success_handler')) {
            $container
                ->findDefinition('sylius.authentication.success_handler')
                ->setClass(AuthenticationSuccessHandler::class);
        }
    }
}
