<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form;
use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;

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
     * @Route("/product/{id}", name="product_show")
     */
    public function showAction(Product $product, Request $request)
    {
    	// pobieramy aktualnie zalogowanego użytkownika
    	$user = $this->getUser();
    	
    	// tworzymy nowy komentarz
    	$comment = new Comment();
    	
    	// przypisujemy produkt do komentarza
    	$comment->setProduct($product);
    	
    	$comment->setUser($user);
    	
    	$form = $this->createForm(new CommentType(), $comment);
    	
    	// wypełnia form builder przesłanymi danymi 
    	$form->handleRequest($request);
    	
    	// jeżeli został form wysłany a user nie jest zalogowany
    	if ( $form->isSubmitted() && !$user ) {
    		$this->addFlash('error', 'Zaloguj się, aby dodać komentarz');
    		return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
    	}
    	
    	if ( $form->isValid() ) {
    		
    		// czy user jest adminem
    		if ( $user->hasRole('ROLE_ADMIN') ) {
    			
    			$comment->setVerified(true);
    		
    		}
    		
    		$em = $this->getDoctrine()->getManager();
    		
    		$em->persist($comment);
    		$em->flush();
    		
    		if ( $user->hasRole('ROLE_ADMIN') ) {
    			$this->addFlash('notice', 'Komentarz pomyślnie zapisany i opublikowany.');
    		} else {
    			$this->addFlash('notice', 'Komentarz pomyślnie zapisany. Oczekuje na zatwierdzenie.');
    		}

    		return $this->redirectToRoute('product_show', array('id'=> $product->getId()));
    		
    	}
    	
    	return $this->render('products/show.html.twig', [
    			'product'   => $product,
    			'form'		=> $form->createView()
    	]);
    }   
     
    /**
     * @Route("/szukaj", name="product_search")
     */    
    public function searchAction(Request $request)
    {
    	$query = $request->query->get('query');

    	$constraint = new \Symfony\Component\Validator\Constraints\NotBlank();
    	
    	$errors = $this->get('validator')->validate($query, $constraint);
    	    	
    	$products = $this->getDoctrine()
    	->getRepository('AppBundle:Product')
    	->createQueryBuilder('p')
    	->select('p')
    	->where('p.name LIKE :query')
    	->orWhere('p.description LIKE :query')
    	->setParameter('query', '%' . $query . '%')
    	->getQuery()
    	->getResult();
    	
    	return $this->render('products/search.html.twig', array(
    			'query' => $query,
    			'products' => $products
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