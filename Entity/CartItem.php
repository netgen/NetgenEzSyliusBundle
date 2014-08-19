<?php

namespace Netgen\EzSyliusBundle\Entity;

use Sylius\Component\Cart\Model\CartItem as BaseCartItem;

class CartItem extends BaseCartItem
{
    private $product;

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct( $product)
    {
        $this->product = $product;
    }
}