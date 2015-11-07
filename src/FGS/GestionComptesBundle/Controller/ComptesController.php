<?php
namespace FGS\GestionComptesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use FGS\GestionComptesBundle\Entity\Compte;
use FGS\GestionComptesBundle\Form\CompteType;

class ComptesController extends Controller
{
    public function indexAction()
    {
		//récupération du manager
    	$em		= $this->getDoctrine()->getManager();

    	//récupération de la liste des comptes et des derniers mouvements liés à ce compte (les deux derniers)
    	$listeComptes	= $em->getRepository('FGSGestionComptesBundle:Compte')->getCompteAndBanqueForUtilisateur($this->getUser()->getId());

		//génération de la vue
    	return $this->render('FGSGestionComptesBundle:Comptes:index.html.twig', array(
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
    		$compte->setUtilisateur($this->getUser());
			$em	=	$this->getDoctrine()->getManager();

			$em->persist($compte);
			$em->flush();
			
    		$session	=	new Session();
    		$session->getFlashBag()->add('success', 'Le compte a bien été ajouté !');
    		
    		return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
    	}
    	
    	return $this->render('FGSGestionComptesBundle:Comptes:ajouter.html.twig', array(
    			"form"=> $form->createView(),
    	));
    
    }
    
    public function gererCompteAction()
    {
    	$utilisateur	= $this->getUser();
    	
    	$em		= $this->getDoctrine()->getManager();
    	
    	$listeComptes	= $em->getRepository('FGSGestionComptesBundle:Compte')->getCompteAndBanqueForUtilisateur($utilisateur->getId());
    	
    	return $this->render('FGSGestionComptesBundle:Comptes:gerer.html.twig', array(
    			'listeComptes'=> $listeComptes
    	));

    }
    
    public function supprimerCompteAction($id)
    {
    	$em		= $this->getDoctrine()->getManager();
    	
    	$compte = $em->find('FGSGestionComptesBundle:Compte', $id);
    	
    	$this->denyAccessUnlessGranted('proprietaire', $compte, 'Vous n\'êtes pas propriétaire de ce compte');
    	
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
		
		$this->denyAccessUnlessGranted('proprietaire', $compte, 'Vous n\'êtes pas propriétaire de ce compte');
		
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
    	 
    	return $this->render('FGSGestionComptesBundle:Comptes:modifier.html.twig', array(
    			"form"=> $form->createView(),
    	));
    }
}
