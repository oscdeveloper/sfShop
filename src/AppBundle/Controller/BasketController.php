<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Product;

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
    public function removeAction($id)
    {
        $session = $this->get('session');
        
        $basket = $session->get('basket', array());
        
        if (!array_key_exists($id, $basket)) {
            $this->addFlash('notice', 'Produkt nie istnieje');
            
            return $this->redirectToRoute('basket');
        }
        
        unset($basket[$id]);

        $session->set('basket', $basket);
        $product = $this->getProduct($id);

        //$this->addFlash('notice', 'Produkt ' . $product['name'] . ' został usunięty z koszyka');

        $this->addFlash('notice', sprintf('Product %s został usunięty z koszyka', $product['name']));
        
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
     * @Route("/koszyk/wyczysc")
     * @Template()
     */
    public function clearAction()
    {
        return array(
                // ...
            );
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

}
