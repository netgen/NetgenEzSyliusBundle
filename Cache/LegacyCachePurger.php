<?php

namespace Netgen\Bundle\EzSyliusBundle\Cache;

use eZ\Bundle\EzPublishLegacyBundle\LegacyMapper\Configuration;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZCache;
use eZCacheHelper;
use eZCLI;
use eZScript;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * Purger for legacy file based cache.
 * Hooks into cache:clear command, which again is used by composer install and update.
 *
 * Overrides Legacy Bridge cache purger to set "quiet" flag to eZCLI,
 * since it crashes Sylius installation procedure.
 */
class LegacyCachePurger implements CacheClearerInterface
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    public function __construct(
        \Closure $legacyKernelClosure,
        Configuration $configurationMapper,
        Filesystem $fs,
        $legacyRootDir,
        SiteAccess $siteAccess
    ) {
        $this->legacyKernelClosure = $legacyKernelClosure;

        // If ezp_extension.php doesn't exist or siteaccess name is "setup", it means that eZ Publish is not yet installed.
        // Hence we deactivate configuration mapper to avoid potential issues (e.g. ezxFormToken which cannot be loaded).
        if (!$fs->exists("$legacyRootDir/var/autoload/ezp_extension.php") || $siteAccess->name === 'setup') {
            $configurationMapper->setEnabled(false);
        }
    }

    /**
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory
     */
    public function clear($cacheDir)
    {
        $this->getLegacyKernel()->runCallback(
            function () {
                $helper = new eZCacheHelper(
                    $cli = eZCLI::instance(),
                    $script = eZScript::instance(
                        array(
                            'description' => 'eZ Publish Cache Handler',
                            'use-session' => false,
                            'use-modules' => false,
                            'use-extensions' => true,
                        )
                    )
                );

                $cli->setIsQuiet(true);
                $helper->clearItems(eZCache::fetchByTag('template,ini,i18n'), 'Legacy file cache (Template, ini and i18n)');
            },
            false,
            false
        );
    }

    /**
     * @return \ezpKernelHandler
     */
    private function getLegacyKernel()
    {
        $closure = $this->legacyKernelClosure;

        return $closure();
    }
}
