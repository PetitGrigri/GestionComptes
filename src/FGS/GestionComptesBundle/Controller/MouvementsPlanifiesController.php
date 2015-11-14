<?php

namespace FGS\GestionComptesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;
use FGS\GestionComptesBundle\Entity\MouvementFinancier;
use FGS\GestionComptesBundle\Entity\MouvementFinancierPlanifie;
use FGS\GestionComptesBundle\Form\Type\MouvementFinancierPlanifieType;


class MouvementsPlanifiesController extends Controller
{
	
	public function ajouterDepensePLanifieeAction(Request $request)
	{
		$mfp			= new MouvementFinancierPlanifie();
		$utilisateur	= $this->getUser();
		$today			= new \DateTime('today');
		
		$cmf = $this->getDoctrine()
					->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
					->getRootMouvementFinancier($utilisateur, CategorieMouvementFinancier::TYPE_DEPENSE);
		
		$mfp->setCategorieMouvementFinancier($cmf);
		$form = $this->createForm(new MouvementFinancierPlanifieType($this->getDoctrine(), $utilisateur->getId()), $mfp);
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$em	=	$this->getDoctrine()->getManager();
			
			$this->checkCoherenceMouvementFinancierPlanifie($mfp);

			//dans le cas ou le mouvement financier planifié commence le jour même on crée aussi le mouvement financier
			if ($mfp->getDateInitiale() == $today) {
				$mf	= $this->createMouvementFinancierFromMouvementFinanciePlanifie($mfp);
				$em->persist($mf);
			}
			
			$em->persist($mfp);
			$em->flush();
			
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La dépense planifié a été prise en compte!');
		
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
		
		return $this->render('FGSGestionComptesBundle:MouvementsPlanifies:ajouter_depense_planifiee.html.twig', array(
				'form'	=>	$form->createView(),
		));
	}
	
	public function ajouterRevenuPLanifieAction(Request $request)
	{
		$mfp			= new MouvementFinancierPlanifie();
		$utilisateur	= $this->getUser();
		$today			= new \DateTime('today');
		
		$cmf = $this->getDoctrine()
					->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
					->getRootMouvementFinancier($utilisateur, CategorieMouvementFinancier::TYPE_REVENU);
		
		$mfp->setCategorieMouvementFinancier($cmf);
		
		$form = $this->createForm(new MouvementFinancierPlanifieType($this->getDoctrine(), $utilisateur->getId()), $mfp);
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$em	=	$this->getDoctrine()->getManager();
			
			$this->checkCoherenceMouvementFinancierPlanifie($mfp);

			//dans le cas ou le mouvement financier planifié commence le jour même on crée aussi le mouvement financier
			if ($mfp->getDateInitiale() == $today) {
				$mf	= $this->createMouvementFinancierFromMouvementFinanciePlanifie($mfp);
				$em->persist($mf);
			}
			
			$em->persist($mfp);
			$em->flush();

			$session	=	new Session();
			$session->getFlashBag()->add('success', 'Le revenu planifié a été prise en compte!');
		
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
		
		return $this->render('FGSGestionComptesBundle:MouvementsPlanifies:ajouter_revenu_planifie.html.twig', array(
				'form'	=>	$form->createView(),
		));
	}
	
	public function voirMouvementFinancierPlanifieAction()
	{
		$utilisateur	= $this->getUser();

		$liste_mouvements_planifies	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:MouvementFinancierPlanifie')
			->getMouvementsFinanciersPlanifiesForUtilisateur($utilisateur->getId());
			
		return $this->render('FGSGestionComptesBundle:MouvementsPlanifies:visualiser_mouvements_planifies.html.twig', array(
				'liste_mouvements_financiers_planifies'	=>	$liste_mouvements_planifies,
		));
	}
	
	public function modifierMouvementFinancierPlanifieAction($id,Request $request)
	{
		$utilisateur	= $this->getUser();
		$today			= new \DateTime('today');
		
		$mfp	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:MouvementFinancierPlanifie')->find($id);
		
		$this->denyAccessUnlessGranted('proprietaire', $mfp, 'Vous n\'avez pas pas le droit de modifier cette planification');
		
		$form = $this->createForm(new MouvementFinancierPlanifieType($this->getDoctrine(), $utilisateur->getId()), $mfp);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$em	=	$this->getDoctrine()->getManager();
				
			$this->checkCoherenceMouvementFinancierPlanifie($mfp);
		
			//dans le cas ou le mouvement financier planifié commence le jour même on crée aussi le mouvement financier
			if ($mfp->getDateInitiale() == $today) {
				$mf	= $this->createMouvementFinancierFromMouvementFinanciePlanifie($mfp);
				$em->persist($mf);
			}
				
			$em->persist($mfp);
			$em->flush();
		
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La dépense planifié a été modifiée !');
		
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_voir_mouvements_planifies"));
		}

		return $this->render('FGSGestionComptesBundle:MouvementsPlanifies:modifier_mouvement_financier_planifie.html.twig', array(
				'form'	=> $form->createView(),
				'type' 	=> $mfp->getCategorieMouvementFinancier()->getType(),
		));
	}
	
	
	public function supprimerMouvementFinancierPlanifieAction($id)
	{
		$em		= $this->getDoctrine()->getManager();
	
		$mfp = $em->find('FGSGestionComptesBundle:MouvementFinancierPlanifie', $id);
	
		$this->denyAccessUnlessGranted('proprietaire', $mfp, 'Vous n\'avez pas pas le droit de supprimer cette planification');
		
		if ($mfp !== null) {
			$em->remove($mfp);
			$em->flush();
	
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'Votre '.$mfp->getCategorieMouvementFinancier()->getType().' a été supprimé !');
		}
		else {
			$session	=	new Session();
			$session->getFlashBag()->add('error', 'TODO.');
		}
		return $this->redirect($this->getRequest()->headers->get('referer'));
	}
	

	private function checkCoherenceMouvementFinancierPlanifie(MouvementFinancierPlanifie $mfp) 
	{
		//vérification du type de la categorie du mouvement financier pour avoir un montant cohérent
		if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE) {
			$mfp->setMontant(-abs($mfp->getMontant()));
		}
		if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_REVENU) {
			$mfp->setMontant(abs($mfp->getMontant()));
		}
	}
	
	private function createMouvementFinancierFromMouvementFinanciePlanifie(MouvementFinancierPlanifie $mfp)
	{
		$mf	= new MouvementFinancier();
		$mf->setCategorieMouvementFinancier($mfp->getCategorieMouvementFinancier());
		$mf->setCompte($mfp->getCompte());
		$mf->setDate($mfp->getDateInitiale());
		$mf->setLibelle($mfp->getLibelle());
		$mf->setMontant($mfp->getMontant());
		$mf->setWasPlanified(true);
		$mf->setPlanified(false);
		
		return $mf;
	}
	
	
}
