<?php

namespace Netgen\Bundle\EzSyliusBundle\Composer;

use eZ\Bundle\EzPublishCoreBundle\Composer\ScriptHandler as BaseScriptHandler;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Composer\Script\CommandEvent;

class ScriptHandler extends BaseScriptHandler
{
    /**
     * Dump minified assets for prod environment under the web root directory.
     *
     * @param $event \Composer\Script\CommandEvent A instance
     */
    public static function dumpAssets( CommandEvent $event )
    {
        try
        {
            parent::dumpAssets( $event );
        }
        catch ( TableNotFoundException $e )
        {
            $options = self::getOptions( $event );
            $appDir = $options['symfony-app-dir'];

            static::executeCommand( $event, $appDir, 'doctrine:schema:create --force' );
            parent::dumpAssets( $event );
        }
    }
}
