<?php

namespace Netgen\EzSyliusBundle\Cart;

use Sylius\Component\Cart\Model\CartItemInterface;
use Sylius\Component\Cart\Resolver\ItemResolverInterface;
use Sylius\Component\Cart\Resolver\ItemResolvingException;
use Netgen\EzSyliusBundle\Entity\EzProduct;

class ItemResolver implements ItemResolverInterface
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function resolve(CartItemInterface $item, $request)
    {
        $productId = $request->query->getInt("id");

        try {
            $product = new EzProduct();
            $product->setEzContentAsProduct($productId, $this->repository);

        } catch (\Exception $e) {
            throw new ItemResolvingException('Requested product was not found');
        }

        // Assign the product to the item and define the unit price.
        $item->setProduct( $product );
        $item->setUnitPrice("222");

        // Everything went fine, return the item.
        return $item;
    }
}