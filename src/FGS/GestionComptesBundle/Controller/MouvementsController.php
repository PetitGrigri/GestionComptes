<?php

namespace FGS\GestionComptesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;
use FGS\GestionComptesBundle\Entity\MouvementFinancier;
use FGS\GestionComptesBundle\Form\Type\MouvementFinancierType;

class MouvementsController extends Controller
{
	public function ajouterDepenseAction(Request $request)
	{
		$mf				= new MouvementFinancier();
		$utilisateur	= $this->getUser();

		$cmf	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->findOneBy(array(
			'type'		=>	CategorieMouvementFinancier::TYPE_DEPENSE,
			'parent'	=>	null,
			'utilisateur'	=>	$utilisateur,
		));

		$mf->setCategorieMouvementFinancier($cmf);
		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine(), $utilisateur->getId()), $mf);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$em			=	$this->getDoctrine()->getManager();
			$session	=	new Session();
			
			$this->checkCoherenceMouvementFinancier($mf);
			$session->getFlashBag()->add('success', ($mf->isPlanified())? 'La dépense est prise en compte. Elle sera effective le : '.$mf->getDate()->format('d/m/Y').' !':'La dépense est prise en compte!');

			$em->persist($mf);
			$em->flush();

			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
    	return $this->render('FGSGestionComptesBundle:Mouvements:ajouter_depense.html.twig', array(
  			'form'	=>	$form->createView(),
    	));
	}
	
	
	public function ajouterRevenuAction(Request $request)
	{
		$mf				= new MouvementFinancier();
		$utilisateur	= $this->getUser();

		$cmf	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->findOneBy(array(
				'type'			=>	CategorieMouvementFinancier::TYPE_REVENU,
				'parent'		=>	null,
				'utilisateur'	=>	$utilisateur
		));
		$mf->setCategorieMouvementFinancier($cmf);
		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine(), $utilisateur->getId()), $mf);
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$em			=	$this->getDoctrine()->getManager();
			$session	=	new Session();
			
			$this->checkCoherenceMouvementFinancier($mf);
			$session->getFlashBag()->add('success', ($mf->isPlanified())? 'Le revenu est pris en compte. Il sera effectif le : '.$mf->getDate()->format('d/m/Y').' !':'Le revenu est pris en compte!');

			$em->persist($mf);
			$em->flush();

			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
		return $this->render('FGSGestionComptesBundle:Mouvements:ajouter_revenu.html.twig', array(
				'form'	=>	$form->createView(),
		));
	}

	
	public function supprimerMouvementFinancierAction($id)
	{
		$em		= $this->getDoctrine()->getManager();
	
		$mf = $em->find('FGSGestionComptesBundle:MouvementFinancier', $id);
		
		$this->denyAccessUnlessGranted('proprietaire', $mf, 'Vous n\'avez pas pas le droit de supprimer ce mouvement financier');
		
		if ($mf !== null) {
			$em->remove($mf);
			$em->flush();
		
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'Votre '.$mf->getCategorieMouvementFinancier()->getType().' a été supprimé !');
		}
		else {
			$session	=	new Session();
			$session->getFlashBag()->add('error', 'TODO.');
		}
		return $this->redirect($this->getRequest()->headers->get('referer'));
	}
	
	public function modifierMouvementFinancierAction($id,Request $request)
	{
		$em			= $this->getDoctrine()->getManager();
		$session	= new Session();
		
		//récupération du mouvement financier que l'on veut modifier, et sa catégorie
		$mf 	= $em->find('FGSGestionComptesBundle:MouvementFinancier', $id);
		$cmf	= $mf->getCategorieMouvementFinancier();
		$user	= $this->getUser();
		
		$this->denyAccessUnlessGranted('proprietaire', $mf, 'Vous n\'avez pas pas le droit de modifier ce mouvement financier');

		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine(), $user->getId()), $mf);
	
		$form->handleRequest($request);
	
		if ($form->isValid()) {
			$this->checkCoherenceMouvementFinancier($mf);
			$em->flush();
			$session->getFlashBag()->add('success', 'La modification de votre '.$cmf->getType().' a été modifié !');
				
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
		
		return $this->render('FGSGestionComptesBundle:Mouvements:modifier_mouvement_financier.html.twig', array(
				'form'	=>	$form->createView(),
		));
	}
	
	
	
	public function voirMouvementFinancierCompteAction($id, $debut = 0, $longueur = 15)
	{
		$Max		= intval($this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte')->getCompteMaxMouvements($id));
		$compte		= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte')->getCompteMouvementAndCategorie($id, $debut, $longueur);
		
		return $this->render('FGSGestionComptesBundle:Mouvements:visualiser_mouvements_compte.html.twig', array(
			'compte'				=> $compte[0],
			'maxMouvementFinanciers'=> $Max,
			'positionActuelle'		=> $debut,
			'longueur'				=> $longueur,
			'id'					=> $id,
		));
	}
	
	public function voirMouvementFinancierCompteMoisAction($id, $annee, $mois)
	{
		$date		= (($annee !== null)&&($mois !== null))? new \DateTime("$annee-$mois"):new \DateTime("now");
		$anneeMois	= $date->format('Y-m');

		$repository	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte');
		
		$compte			= $repository->getCompteMouvementAndCategorieMois($id, $anneeMois);
		
		$montantCategorie	= $repository->getMontantForEachCategorie($id, $anneeMois);
		
		$totalDepenseAndRevenuNotPlanified		= $repository->getDepenseAndRevenuNotPlanified($id, $anneeMois);
		$totalDepenseAndRevenuPlanified			= $repository->getDepenseAndRevenuPlanified($id, $anneeMois);
		
		return $this->render('FGSGestionComptesBundle:Mouvements:visualiser_mouvements_compte_mois.html.twig', array(
				'compte'					=> $compte[0],
				'id'						=> $id,
				'date'						=> $date,
				'totauxParCategorie'		=> $montantCategorie,
				'totalDepensePlanified'		=> isset($totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_DEPENSE]) ? $totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_DEPENSE] : 0,
				'totalRevenuPlanified'		=> isset($totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_REVENU]) ?$totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_REVENU] : 0,
				'totalDepenseNotPlanified'	=> isset($totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_DEPENSE]) ? $totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_DEPENSE] : 0,
				'totalRevenuNotPlanified'	=> isset($totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_REVENU]) ?$totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_REVENU] : 0,	
		));
	}
	
	public function voirMouvementFinancierCompteAnneeAction($id, $annee)
	{
		$date		= ($annee !== null)? new \DateTime("$annee-01"):new \DateTime("now");

		$data_annee_mois	= array();
		
		for ($compteurMois =1; $compteurMois<=12; $compteurMois++) {
			$data_annee_mois[$date->format('Y').'-'.str_pad($compteurMois,2,'0',STR_PAD_LEFT)]	=	array(
				"montant_depense" => 0,
				"montant_revenu" => 0,
				"date" => new \DateTime($date->format('Y').'-'.$compteurMois),
			);
		}

		$repository	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte');

		$BilanAnnuelDepensesRevenu		= $repository->getDepenseAndRevenuByTypeForYear($id, $date->format('Y'));
		$compte							= $repository->find($id);
		
		foreach ($BilanAnnuelDepensesRevenu as $data) {
			if ($data["type"] == CategorieMouvementFinancier::TYPE_DEPENSE) {
				$data_annee_mois[$data["annee_mois"]]["montant_depense"] = $data["total"];
			}
			else {
				$data_annee_mois[$data["annee_mois"]]["montant_revenu"] = $data["total"];
			}
		}
		
		return $this->render('FGSGestionComptesBundle:Mouvements:visualiser_mouvements_compte_annee.html.twig', array(
				'depense_annee'		=> $data_annee_mois,
				'compte'			=> $compte,
				'date' 				=> $date,
		));
	}
	
	public function checkMouvementFinancierAction($id)
	{
		$em 	= $this->getDoctrine()->getEntityManager();
		$mf 	= $em->getRepository('FGSGestionComptesBundle:MouvementFinancier')->find($id);
		
		if ($mf->getCompte()->getUtilisateur()->getId() != $this->getUser()->getId()) {
			$session	= $this->getRequest()->getSession();
			$session->getFlashBag()->add('error', 'Vous ne pouvez pas checker ce mouvement financier !');
		}
		else {
			$mf->setCheckBanque(($mf->getCheckBanque()) ? false : true);
			$em->flush();
		}
		return $this->redirect($this->getRequest()->headers->get('referer'));
	}
	
	private function checkCoherenceMouvementFinancier(MouvementFinancier $mf)
	{
		$today		= new \DateTime('today');
		
		//vérification de la date pour savoir si le mouvement financier sera planifié ou pas
		if ($mf->getDate() > $today) {
			$mf->setPlanified(true);
		}
		else {
			$mf->setPlanified(false);
		}
		
		//vérification du type de la categorie du mouvement financier pour avoir un montant cohérent
		if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE) {
			$mf->setMontant(-abs($mf->getMontant()));
		}
		if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_REVENU) {
			$mf->setMontant(abs($mf->getMontant()));
		}
	}
}
