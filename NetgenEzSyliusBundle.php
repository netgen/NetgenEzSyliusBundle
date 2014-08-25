<?php

namespace Netgen\EzSyliusBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyBundleInterface;

class NetgenEzSyliusBundle extends Bundle implements LegacyBundleInterface
{
    public function getLegacyExtensionsNames()
    {
        return array('ngsyliusprice');
    }
}
