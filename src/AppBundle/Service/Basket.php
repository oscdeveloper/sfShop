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
		
		if ($product->getAmount() <= 0 ) {
			//$this->addFlash('notice', 'Nie ma wystarczającej ilości.');
			//return $this->redirectToRoute('products_list');
			throw new \Exception('Produkt jest niedostępny.');
		}		
		
		$products = $this->getProducts();
		
		if (!array_key_exists($product->getId(), $products)) {

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
	
	public function remove(Product $product)
	{
		$products = $this->getProducts();
		
		if (!array_key_exists($product->getId(), $products)) {
			
			throw new \Exception(sprintf('Produktu %s nie ma w koszyku.', $product->getName()));
			
		}
		
		unset($products[$product->getId()]);
	
		$this->session->set('basket', $products);
	
		return $this;
	
	}

	public function clear()
	{
	
		$this->session->remove('basket');
	
		return $this;
	
	}
	
	public function countProducts() {
		
		$products = $this->getProducts();
		
		$count = 0;
		
		foreach ($products as $product) {
		
			$count += $product['quantity'];
			
		}
		
		return $count;
		
	}
	
	public function getTotalValue() {
	
		$products = $this->getProducts();
	
		$totalValue = 0;
	
		foreach ($products as $product) {
	
			$totalValue += $product['quantity'] * $product['price'];
				
		}
		
		$totalValue = number_format($totalValue, 2, ',', '') . ' zł';
		
		return $totalValue;
	
	}	
	
}