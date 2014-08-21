<?php

namespace Netgen\EzSyliusBundle\Cart;

use Sylius\Component\Cart\Model\CartItemInterface;
use Sylius\Component\Cart\Resolver\ItemResolverInterface;
use Sylius\Component\Cart\Resolver\ItemResolvingException;
use Netgen\EzSyliusBundle\Entity\EzProduct;

class ItemResolver implements ItemResolverInterface
{
    private $repository;
    private $allowed_content_types;
    //private $xmlToHtml5Converter;

    public function __construct($repository, $allowed_content_types )
    {
        $this->repository = $repository;
        $this->allowed_content_types = $allowed_content_types;
    }

    public function resolve(CartItemInterface $item, $request)
    {
        $productId = $request->query->getInt("id");

        try {
            $product = new EzProduct();
            $product->setEzContentAsProduct($productId, $this->repository, $this->allowed_content_types);

        } catch (\Exception $e) {
            throw new ItemResolvingException('Requested product was not found');
        }

        // Assign the product to the item and define the unit price.
        $item->setProduct( $product );
        //$item->setUnitPrice("222");
        $item->setUnitPrice($product->getPrice());

        // Everything went fine, return the item.
        return $item;
    }
}