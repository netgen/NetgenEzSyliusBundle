<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netgen\EzSyliusBundle\OrderProcessing;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\OrderProcessing\InventoryHandlerInterface;


class DummyInventoryHandler implements InventoryHandlerInterface
{

    public function __construct() {}


    /**
     * {@inheritdoc}
     */
    public function processInventoryUnits(OrderItemInterface $item)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function holdInventory(OrderInterface $order)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function releaseInventory(OrderInterface $order)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateInventory(OrderInterface $order)
    {
    }

}
