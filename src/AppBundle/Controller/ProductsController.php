<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Category;
use AppBundle\Form\ProductType;
use AppBundle\Entity\ProductOrder;

class ProductsController extends Controller
{
    /**
     * @Route("/produkty/{id}", name="products_list", defaults={"id" = false}, requirements={"id":"\d+"})
     */
    public function indexAction(Request $request, Category $category = null)
    {
        $getProductsQuery = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->getProductsQuery($category);
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $getProductsQuery,
            $request->query->get('page', 1),
            8
        );

        return $this->render('products/index.html.twig', [
            'products' => $pagination,
        ]);
    }
    
    /**
     * @Route("/produkty/dodaj", name="products_add")
     */
    public function addAction(Request $request)
    {
    	$form = $this->createForm(new ProductType());
    	$form->handleRequest($request);
    	
    	return $this->render('products/add.html.twig', array(
    			'form' => $form->createView(),
    	));
    }    
    
    public function orderAction()
    {
    	// pobranie usługi ksozyka
    	$basket = $this->get('basket');
    	$products = $basket->getProducts();
    	
    	// bierzemy identyfikatory z sesji dla bazy danych
    	$product_ids = array_keys($products);
    	
    	// wybieramy produkty z bazy danych na podstawie id
    	$products = $this->getDoctrine()->getRepository('AppBundle:Product')
    		->find($product_ids);
    	
    	// tworzymy nowy obiekt zamówienia
    	$order = new ProductOrder();
    	// przypisujemy poszczególne produkty do zamówienia na poziomie encji
    	foreach($products as $product) {
    		$order->addProduct($product);
    	}
    	
    	// pobieramy Entity manager by móc zapisać encje w bazie
    	$em = $this->getDoctrine()->getManager();
    	// dodajemy encje zamówienia do rejestru Doctrine
    	$em->persist($order);
    	// wysłanie zmian do bazy danych w tym nowo-utowrzonego produktu
    	$em->flush();
    	
    }

}