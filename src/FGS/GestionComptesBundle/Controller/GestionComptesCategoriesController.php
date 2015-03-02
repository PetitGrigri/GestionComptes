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




class GestionComptesCategoriesController extends Controller
{   
    public function gererCategoriesAction()
    {
    	$em					= $this->getDoctrine()->getManager();
    	$listeCategories	= $em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->getTreeCategories();

    	
    	return $this->render('FGSGestionComptesBundle:GestionComptes:gerer_categories.html.twig', array(
			'listeCategories'	=> $listeCategories,

    	));
	}

	
	public function ajouterCategorieAction(Request $request)
	{
		$cmf	=	new CategorieMouvementFinancier();
		
		$form = $this->createForm(new CategorieMouvementFinancierType($this->getDoctrine()), $cmf);
		
		$form->handleRequest($request);

		if ($cmf->getParent() != null) {
			$cmf->setOrdre(count($cmf->getParent()->getChildrens())+1);
			$cmf->setType($cmf->getParent()->getType());
		}
		else {
			//normallement on ne peux pas arriver ici
			$cmf->setOrdre(1);
		}
		
			
		if ($form->isValid())
		{
			$em	=	$this->getDoctrine()->getManager();
		
			$em->persist($cmf);
			$em->flush();
				
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La catégorie a bien été ajoutée!');
		
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
		}		
		
		return $this->render('FGSGestionComptesBundle:GestionComptes:ajouter_categorie.html.twig', array(
				'form'	=> $form->createView(),
		));
	}

	public function modifierCategorieAction($id, Request $request)
	{
		$em	= $this->getDoctrine()->getManager();
		 
		$cmf = $em->find('FGSGestionComptesBundle:CategorieMouvementFinancier', $id);
	
		$form = $this->createForm(new CategorieMouvementFinancierType($this->getDoctrine()), $cmf);
		
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
			$em->persist($cmf);
			$em->flush();
			 
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La catégorie a bien été modifié !');
			 
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
		}
	
	
		return $this->render('FGSGestionComptesBundle:GestionComptes:modifier_categorie.html.twig', array(
				"form"=> $form->createView(),
		));
	}
	
	public function supprimerCategorieAction($id)
	{
		$em		= $this->getDoctrine()->getManager();
			
		$cmf = $em->find('FGSGestionComptesBundle:CategorieMouvementFinancier', $id);
		
		if ($cmf != null)
		{
			$em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->decreaseOrdreAfter($cmf->getParent(), $cmf->getOrdre());
			
			$em->remove($cmf);

			$em->flush();
	
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La catégorie a bien été supprimé !');
		}
		else
		{
			$session	=	new Session();
			$session->getFlashBag()->add('error', 'Erreur lors de la tentative de suppression.');
		}
	
		return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
	
	}
	
	public function monterCategorieAction($id)
	{
		$em	=$this->getDoctrine()->getManager();
		
		$cmf = $em->find('FGSGestionComptesBundle:CategorieMouvementFinancier', $id);
		
		if (($cmf != null) && ($cmf->getOrdre() > 1))
		{
			$test =  $em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->increseOrdrePredecessor($cmf);
			
			if ($test == 1)
			{
				$cmf->setOrdre($cmf->getOrdre() - 1);			
				$em->flush();
				
				$session	=	new Session();
				$session->getFlashBag()->add('success', 'La catégorie a été déplacé !');
			}
			else 
			{
				throw new GestionComptesCategorieMouvementFinancierException('Impossible de monter votre Catégorie', 500);
			}
		}
		else
		{
			throw new GestionComptesCategorieMouvementFinancierException('Impossible de monter votre Catégorie', 500);
		}
		
		return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
	}
	
	
	
	
	
	public function descendreCategorieAction($id)
	{
		$em	=$this->getDoctrine()->getManager();
		
		$cmf = $em->find('FGSGestionComptesBundle:CategorieMouvementFinancier', $id);
		
		if ($cmf != null)
		{
			$test =  $em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->decreaseOrdreSuccessor($cmf);
			
			if ($test == 1)
			{
				$cmf->setOrdre($cmf->getOrdre() + 1);			
				$em->flush();
				
				$session	=	new Session();
				$session->getFlashBag()->add('success', 'La catégorie a été déplacé !');
			}
			else 
			{
				throw new GestionComptesCategorieMouvementFinancierException('Impossible de monter votre Catégorie', 500);
			}
		}
		else
		{
			throw new GestionComptesCategorieMouvementFinancierException('Impossible de monter votre Catégorie', 500);
		}
		
		return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
	}
}
