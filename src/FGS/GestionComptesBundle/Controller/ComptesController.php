<?php
namespace FGS\GestionComptesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use FGS\GestionComptesBundle\Entity\Compte;
use FGS\GestionComptesBundle\Form\Type\CompteType;
use FGS\GestionComptesBundle\Security\Authorization\Voter\CompteOrCategorieVoter;


class ComptesController extends Controller
{
    public function indexAction()
    {
		//récupération du manager
    	$em		= $this->getDoctrine()->getManager();

    	//récupération de la liste des comptes et des derniers mouvements liés à ce compte (les deux derniers)
    	$listeComptes	= $em->getRepository('FGSGestionComptesBundle:Compte')->getCompteAndBanqueForUtilisateur($this->getUser()->getId());

    	if (!empty($listeComptes)) {
	    	return $this->render('FGSGestionComptesBundle:Comptes:index.html.twig', array(
	    			'listeComptes'	=> $listeComptes,
	     	));
    	}
    	else {
    		return $this->redirectToRoute('fgs_gestion_comptes_welcome');
    	}

    }
    
    public function ajouterCompteAction(Request $request)
    {
    	$compte	=	new Compte();
    	
    	$form = $this->createForm(CompteType::class, $compte);
    	
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
    			'listeComptes'=> $listeComptes, 
    			''
    	));
    }

    public function supprimerCompteAction(Request $request)
    {
    	//récupération du "mini formulaire" contenant l'id de ce que l'on veut supprimer (avec le tocker crsf)
    	$form = $this->createDeletePOSTForm();
    	
    	$form->handleRequest($request);
    	
    	if ($form->isValid())
    	{
    		//récupération de l'id du mouvement financier
    		$id = $form->getViewData()['id'];
    		
	    	$em		= $this->getDoctrine()->getManager();
	    	
	    	$compte = $em->find('FGSGestionComptesBundle:Compte', $id);

	    	$this->denyAccessUnlessGranted(CompteOrCategorieVoter::PROPRIETAIRE, $compte, 'Vous n\'êtes pas propriétaire de ce compte');
	    	
	    	if ($em->getRepository('FGSGestionComptesBundle:Compte')->deleteCompteById($id)	=== 1)
	    	{
		    	$em->flush();
		    	
		    	$session	=	new Session();
		    	$session->getFlashBag()->add('success', 'Le compte a bien été supprimé !');
	    	}
    	}
    	return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_compte"));
    
    }
    
    public function modifierCompteAction($id, Request $request)
    {
    	
    	$em	= $this->getDoctrine()->getManager();
    	
		$compte = $em->find('FGSGestionComptesBundle:Compte', $id);
		
		$this->denyAccessUnlessGranted(CompteOrCategorieVoter::PROPRIETAIRE, $compte, 'Vous n\'êtes pas propriétaire de ce compte');
		
    	$form = $this->createForm(CompteType::class, $compte);
    	
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
	public function genererFormDeleteAction()
	{
		return $this->render('FGSGestionComptesBundle:Comptes:generer_formulaire_delete.html.twig', array(
			'form_delete'	=> $this->createDeletePOSTForm()->createView(),
		));
	}
	
	private function createDeletePOSTForm()
	{
		return $this->createEmptyPostForm('fgs_gestion_comptes_supprimer_compte', 'delete_compte');
	}
	
    private function createEmptyPOSTForm($route, $idForm)
    {
    	return $this->createFormBuilder(array('id'=>null), array('attr' => array('id'=>$idForm)))
	    	->setAction($this->generateUrl($route))
	    	->add('id', HiddenType::class)
	    	->setMethod('POST')
	    	->getForm();
    }
}
