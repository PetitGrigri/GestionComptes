<?php
namespace FGS\GestionComptesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use FGS\GestionComptesBundle\Entity\Compte;
use FGS\GestionComptesBundle\Entity\TypeCompte;
use FGS\GestionComptesBundle\Entity\Banque;
use FGS\GestionComptesBundle\Form\CompteType;
use Symfony\Component\HttpFoundation\Response;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;
use Symfony\Component\Form\RequestHandlerInterface;
use FGS\GestionComptesBundle\Form\CategorieMouvementFinancierType;
use Symfony\Component\Config\Definition\Exception\Exception;
use FGS\GestionComptesBundle\Exceptions\GestionComptesException;
use FGS\GestionComptesBundle\Exceptions\GestionComptesCategorieMouvementFinancierException;

class GestionComptesController extends Controller
{
    public function indexAction()
    {
    	$em		= $this->getDoctrine()->getManager();
    	
    	$listeComptes	= $em->getRepository('FGSGestionComptesBundle:Compte')->getCompteAndBanque();
    	
    	//\Doctrine\Common\Util\Debug::dump($listeComptes);
    	return $this->render('FGSGestionComptesBundle:GestionComptes:index.html.twig', array(
    			'listeComptes'=> $listeComptes
    	));

    }
    
    public function ajouterCompteAction(Request $request)
    {
    	$compte	=	new Compte();
    	

    	$form = $this->createForm(new CompteType(), $compte)
    				->add('sauver', 'submit', array('label'=>'Ajouter ce compte'))
    				->add('effacer','reset');

    	$form->handleRequest($request);
    	
    	if ($form->isValid())
    	{
			$em	=	$this->getDoctrine()->getManager();

			$em->persist($compte);
			$em->flush();
			
    		$session	=	new Session();
    		$session->getFlashBag()->add('success', 'Le compte a bien été ajouté !');
    		
    		return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
    	}
    	
    	return $this->render('FGSGestionComptesBundle:GestionComptes:ajouter.html.twig', array(
    			"form"=> $form->createView(),
    	));
    
    }
    
    public function gererCompteAction()
    {
    	$em		= $this->getDoctrine()->getManager();
    	
    	$listeComptes	= $em->getRepository('FGSGestionComptesBundle:Compte')->getCompteAndBanque();
    	
    	return $this->render('FGSGestionComptesBundle:GestionComptes:gerer.html.twig', array(
    			'listeComptes'=> $listeComptes
    	));

    }
    
    public function supprimerCompteAction($id)
    {
    	$em		= $this->getDoctrine()->getManager();
    	
    	if ($em->getRepository('FGSGestionComptesBundle:Compte')->deleteCompteById($id)	=== 1)
    	{
	    	$em->flush();
	    	
	    	$session	=	new Session();
	    	$session->getFlashBag()->add('success', 'Le compte a bien été supprimé !');
    	}
    	else 
    	{
    		$session	=	new Session();
    		$session->getFlashBag()->add('error', 'Erreur lors de la tentative de suppression.');
    	}
	    	
    	return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_compte"));
    
    }
    
    public function modifierCompteAction($id, Request $request)
    {
    	
    	$em	= $this->getDoctrine()->getManager();
    	
		$compte = $em->find('FGSGestionComptesBundle:Compte', $id);
		
		
    	$form = $this->createForm(new CompteType(), $compte)
    	    		->add('sauver', 'submit', array('label'=>'Modifier ce compte'))
    				->add('effacer','reset');;
    	
    	$form->handleRequest($request);
    	 
    	if ($form->isValid())
    	{
    		$em->persist($compte);
    		$em->flush();
    			
    		$session	=	new Session();
    		$session->getFlashBag()->add('success', 'Le compte a bien été modifié !');
    	
    		return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
    	}
    	 
    	return $this->render('FGSGestionComptesBundle:GestionComptes:modifier.html.twig', array(
    			"form"=> $form->createView(),
    	));
    }
}
