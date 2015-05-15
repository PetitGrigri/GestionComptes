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




class CategoriesController extends Controller
{   
    public function gererCategoriesAction()
    {
    	$em					= $this->getDoctrine()->getManager();
    	$utilisateur		= $this->getUser();
    	
    	$listeCategories	= $em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->getTreeCategoriesForUtilisateur($utilisateur->getId());

    	
    	return $this->render('FGSGestionComptesBundle:Categories:gerer_categories.html.twig', array(
			'listeCategories'	=> $listeCategories,

    	));
	}

	
	public function ajouterCategorieAction(Request $request)
	{
		$cmf			= new CategorieMouvementFinancier();
		
		$form = $this->createForm(new CategorieMouvementFinancierType($this->getDoctrine(), $this->getUser()->getId()), $cmf);
		
		$form->handleRequest($request);

		//\Doctrine\Common\Util\Debug::dump($cmf);
		
		$cmf->setUtilisateur($this->getUser());
		
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
		
		return $this->render('FGSGestionComptesBundle:Categories:ajouter_categorie.html.twig', array(
				'form'	=> $form->createView(),
		));
	}

	public function modifierCategorieAction($id, Request $request)
	{
		$em	= $this->getDoctrine()->getManager();
		 
		$cmf = $em->find('FGSGestionComptesBundle:CategorieMouvementFinancier', $id);
	
		if ($cmf->getUtilisateur()->getId() != $this->getUser()->getId())
		{
			$session	=	new Session();
			$session->getFlashBag()->add('error', 'Vous ne pouvez pas modifier cette catégorie !');
			
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
		}
		
		$form = $this->createForm(new CategorieMouvementFinancierType($this->getDoctrine(), $this->getUser()), $cmf);
		
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
			$em->persist($cmf);
			$em->flush();
			 
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La catégorie a bien été modifié !');
			 
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
		}
	
	
		return $this->render('FGSGestionComptesBundle:Categories:modifier_categorie.html.twig', array(
				"form"=> $form->createView(),
		));
	}
	
	public function supprimerCategorieAction($id)
	{
		$em					= $this->getDoctrine()->getManager();
		$listeCmfIdImpacte	= array();
		$session			=	new Session();

		//récupération de la catégorie à supprimer
		$cmfASupprimer = $em->find('FGSGestionComptesBundle:CategorieMouvementFinancier', $id);
		
		//Vérification du propriétaire du Cmf et de l'existance d'un parent
		if (($cmfASupprimer->getUtilisateur()->getId() != $this->getUser()->getId()) || (!$cmfASupprimer->hasParent())) 
		{
			$session->getFlashBag()->add('error', 'Vous ne pouvez pas modifier cette catégorie !');
		}
		else 
		{
			//récupération de la liste des Cmf impactés par la suppression de la catégorie (cmf, cmf enfants...)
			$listeCmfImpacte	= $em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
				->getFlatTreeCategoriesForCmf($cmfASupprimer);
	
			//récupération de tout les mf de la liste récupérés
			foreach ($listeCmfImpacte as $cmf)
			{
				$listeCmfIdImpacte[]	= $cmf->getId();
			}
	
			//mise à jour des mouvements financiers lié au cmf à supprimer (tout les mf prendront la cmf parente de la cmf à supprimer)
			$em->getRepository('FGSGestionComptesBundle:MouvementFinancier')
				->updateMouvementFiancierWithCmfFromList($cmfASupprimer->getParent(), $listeCmfIdImpacte);
	
			//suppression de la liste des Cmf
			$em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
				->removeCmfFromIdList($listeCmfIdImpacte);

			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La catégorie a bien été supprimé !');
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
