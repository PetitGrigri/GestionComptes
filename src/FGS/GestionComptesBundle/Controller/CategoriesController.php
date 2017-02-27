<?php

namespace FGS\GestionComptesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;
use FGS\GestionComptesBundle\Form\Type\CategorieMouvementFinancierType;
use FGS\GestionComptesBundle\Security\Authorization\Voter\CompteOrCategorieVoter;



class CategoriesController extends Controller
{   
    public function gererCategoriesAction()
    {
    	$em					= $this->getDoctrine()->getManager();
    	$utilisateur		= $this->getUser();
    	
    	$listeCategories	= $em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->getTreeCategoriesForUtilisateur($utilisateur->getId());
    	
    	return $this->render('FGSGestionComptesBundle:Categories:gerer_categories.html.twig', array(
			'listeCategories'	=> $listeCategories,
    		'form_delete'		=> $this->createDeletePOSTForm()->createView(),

    	));
	}

	
	public function ajouterCategorieAction(Request $request)
	{
		$cmf			= new CategorieMouvementFinancier();

		$form = $this->createForm(CategorieMouvementFinancierType::class, $cmf);


		$form->handleRequest($request);

		$cmf->setUtilisateur($this->getUser());
		
		if ($cmf->getParent() !== null) {
			$cmf->setOrdre(count($cmf->getParent()->getChildrens())+1);
			$cmf->setType($cmf->getParent()->getType());
		}
		else {
			//normallement on ne peux pas arriver ici
			$cmf->setOrdre(1);
		}

		if ($form->isValid()) {
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
	
		$this->denyAccessUnlessGranted(CompteOrCategorieVoter::PROPRIETAIRE, $cmf, 'Vous n\'êtes pas le propriétaire de cette catégorie');
		
		if ($cmf->getUtilisateur()->getId() != $this->getUser()->getId())
		{
			$session	=	new Session();
			$session->getFlashBag()->add('error', 'Vous ne pouvez pas modifier cette catégorie !');
			
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
		}
		
		$form = $this->createForm(CategorieMouvementFinancierType::class, $cmf);
		
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
	
	public function supprimerCategorieAction(Request $request)
	{
		//récupération du "mini formulaire" contenant l'id de ce que l'on veut supprimer (avec le tocker crsf)
		$form = $this->createDeletePOSTForm();
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			//récupération de l'id du mouvement financier
			$id = $form->getViewData()['id'];

			$em					= $this->getDoctrine()->getManager();
			$listeCmfIdImpacte	= array();
			$session			= new Session();

			//récupération de la catégorie à supprimer
			$cmfASupprimer = $em->find('FGSGestionComptesBundle:CategorieMouvementFinancier', $id);
			
			$this->denyAccessUnlessGranted(CompteOrCategorieVoter::PROPRIETAIRE, $cmfASupprimer, 'Vous n\'êtes pas le propriétaire de cette catégorie');
			
			//Vérification de l'existance d'un parent (si pas de parent : catégorie mère non supprimable)
			if (!$cmfASupprimer->hasParent()) {
				$session->getFlashBag()->add('error', 'Vous ne pouvez pas modifier cette catégorie !');
			}
			else {
				//récupération de la liste des Cmf impactés par la suppression de la catégorie (cmf, cmf enfants...)
				$listeCmfImpacte	= $em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->getFlatTreeCategoriesForCmf($cmfASupprimer);
		
				//récupération de tout les mf de la liste récupérés
				foreach ($listeCmfImpacte as $cmf) {
					$listeCmfIdImpacte[]	= $cmf->getId();
				}
		
				//mise à jour des mouvements financiers lié au cmf à supprimer (tout les mf prendront la cmf parente de la cmf à supprimer)
				$em->getRepository('FGSGestionComptesBundle:MouvementFinancier')->updateMouvementFiancierWithCmfFromList($cmfASupprimer->getParent(), $listeCmfIdImpacte);
		
				//suppression de la liste des Cmf
				$em->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->removeCmfFromIdList($listeCmfIdImpacte);

				$session->getFlashBag()->add('success', 'La catégorie a bien été supprimé !');
			}
		}
		
		return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
	}
	
	
	/**
	 * Permet de monter une catégorie
	 * Un test dans la vue de gestion des catégories empêche de lancer cette action s'il n'y a pas de catégorie ayant un ordre plus bas que celle qu'on souhaite modifier
	 * Dans le cas contraire deplacerCategorie provoquera un flashBag
	 * @param int $id
	 */
	public function monterCategorieAction($id)
	{
		return $this->deplacerCategorie($id, +1);
	}

	
	/**
	 * Permet de descendre une catégorie
	 * Un test dans la vue de gestion des catégories empêche de lancer cette action s'il n'y a pas de catégorie ayant un ordre plus haut que celle qu'on souhaite modifier
	 * Dans le cas contraire deplacerCategorie provoquera un flashBag
	 * @param int $id
	 */
	public function descendreCategorieAction($id)
	{
		return $this->deplacerCategorie($id, -1);
	}
	
	/**
	 * Permet de monter ou descendre une catégorie
	 * @param int $id 
	 * @param int $modification
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	private function deplacerCategorie($id, $modification)
	{
		$repository	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier');
	
		$cmf 		= $repository->find($id);
	
		$this->denyAccessUnlessGranted(CompteOrCategorieVoter::PROPRIETAIRE, $cmf, 'Vous n\'êtes pas le propriétaire de cette catégorie');
	
		$cmfPredecessorOrSuccessor	= $repository->findOneBy(array(
				'ordre' 		=> $cmf->getOrdre()-$modification,
				'utilisateur'	=> $cmf->getUtilisateur(),
				'parent'		=> $cmf->getParent(),
		));
		if ($cmfPredecessorOrSuccessor !== null)
		{
			$repository->switchOrdre($cmf, $cmfPredecessorOrSuccessor);
		}
		else 
		{
			$session	=	new Session();
			$session->getFlashBag()->add('error', 'Impossible de déplacer cette catégorie');
		}
	
		return $this->redirect($this->generateUrl("fgs_gestion_comptes_gerer_categories"));
	}
	
	//voir si on peut faire un trait
	private function createEmptyPOSTForm($route, $idForm)
	{
		return $this->createFormBuilder(array('id'=>null), array('attr' => array('id'=>$idForm)))
			->setAction($this->generateUrl($route))
			->add('id', HiddenType::class)
			->setMethod('POST')
			->getForm();
	}
	private function createDeletePOSTForm()
	{
		return $this->createEmptyPostForm('fgs_gestion_comptes_supprimer_categorie', 'delete_cat');
	}
}
