<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\Product;

class Basket 
{
	private $session;
	
	public function __construct(Session $session)
	{
		$this->session = $session;
	}
	
	public function getProducts()
	{
		return $this->session->get('basket', array());
	}
	
	public function add(Product $product, $quantity = 1)
	{
		$products = $this->getProducts();
		
		if (!array_key_exists($product->getId(), $product)) {

			$products[$product->getId()] = array(
				'id' 		=> $product->getId(),
				'name' 		=> $product->getName(),
				'price'		=> $product->getPrice(),
				'quantity'	=> 0
			);
		
		}
		
		$products[$product->getId()]['quantity'] += $quantity;
		
		$this->session->set('basket', $products);
		
		return $this;
		
	}
	
}

/* $session = $request->getSession();

$basket = $session->get('basket', array());
$products = $this->getProducts();

$productsInBasket = array();
foreach ($basket as $id => $b) {
	$productsInBasket[] = $products[$id];
} */