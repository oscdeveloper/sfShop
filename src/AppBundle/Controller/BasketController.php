<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Product;
use Assetic\Exception\Exception;

class BasketController extends Controller
{
    /**
     * @Route("/koszyk", name="basket")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array(
            'basket' => $this->get('basket'),
        	'countProducts' => $this->countProducts(),
        	'getTotalValue' => $this->getTotalValue()
        );
    }

    /**
     * @Route("/koszyk/{id}/dodaj", name="basket_add")
     */
    public function addAction(Product $product)
    {
    	$basket = $this->get('basket');
    	$basket->add($product);
    	
        $this->addFlash('notice', sprintf('Produkt "%s" został dodany do koszyka', $product->getName()));

        return $this->redirectToRoute('basket');
    }

    /**
     * @Route("/koszyk/{id}/usun", name="basket_remove")
     */
    public function removeAction(Product $product)
    {

    	$basket = $this->get('basket');
    	
    	try {
    		$basket->remove($product);
    	
        	$this->addFlash('notice', sprintf('Produkt "%s" został usunięty z koszyka', $product->getName()));
        
    	} catch (\Exception $e) {
    		
    		$this->addFlash('notice', $e->getMessage());
    	
    	}	
    	
        return $this->redirectToRoute('basket');
    
    }

    /**
     * @Route("/koszyk/{id}/zaktualizuj-ilosc/{quantity}")
     * @Template()
     */
    public function updateAction($id, $quantity)
    {
        return array(
                // ...
            );
    }

    /**
     * @Route("/koszyk/wyczysc", name="basket_clear")
     * @Template()
     */
    public function clearAction()
    {
    	
    	$this
    	->get('basket')
    	->clear();
    	
    	$this->addFlash('notice', 'Koszyk został pomyślnie wyczyszczony.');
    	
    	return $this->redirectToRoute('basket');

    }

    /**
     * @Route("/koszyk/kup")
     * @Template()
     */
    public function buyAction()
    {
        return array(
                // ...
            );
    }

    private function getProducts()
    {
        $file = file('product.txt');
        $products = array();
        foreach ($file as $p) {
            $e = explode(':', trim($p));
            $products[$e[0]] = array(
                'id' => $e[0],
                'name' => $e[1],
                'price' => $e[2],
                'desc' => $e[3],
            );
        }

        return $products;
    }

    private function getProduct($id)
    {
        $products = $this->getProducts();

        return $products[$id];
    }
    
    public function countProducts() {
    	
    	$basket = $this->get('basket');
    	
    	return $basket->countProducts();    	
    	
    }
    
    public function getTotalValue() {
    	 
    	$basket = $this->get('basket');
    	 
    	return $basket->getTotalValue();
    	 
    } 

    public function boxAction () {
    	
    	return $this->render('AppBundle:Basket:box.html.twig', array(
    			'basket' => $this->get('basket'),
    	));   	
    	
    }

}
