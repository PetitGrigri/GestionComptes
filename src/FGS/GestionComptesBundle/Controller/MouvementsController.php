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
		$date				= (($annee !== null)&&($mois !== null))? new \DateTime("$annee-$mois"):new \DateTime("now");
		$date_moins_1_mois 	= clone $date; 
		$date_plus_1_mois	= clone $date;
		
		$date_moins_1_mois->modify('-1 month');
		$date_plus_1_mois->modify('+1 month');
		
		$anneeMois	= $date->format('Y-m');

		$repository	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte');
		
		$compte			= $repository->getCompteMouvementAndCategorieMois($id, $anneeMois);
		
		//récupération de tout les totaux pour chaque catégories		
		$montantCategorie	= $repository->getMontantForEachCategorie($id, $anneeMois);
		//création d'un callback de filtre pour ne transmettre que  les totaux de type "dépense"
		$callback_depense = function($array_data) {
			return ($array_data['total'] <0);
		};
		
		$totalDepenseAndRevenuNotPlanified		= $repository->getDepenseAndRevenuNotPlanified($id, $anneeMois);
		$totalDepenseAndRevenuPlanified			= $repository->getDepenseAndRevenuPlanified($id, $anneeMois);
		
		$nbPlanified	= 0;
		$nbNotPlanified = 0;
		
		foreach ($compte[0]->getMouvementFinanciers() as $mf) {
			if ($mf->isPlanified()) {
				$nbPlanified++;
			} else {
				$nbNotPlanified++;
			}
		}

		return $this->render('FGSGestionComptesBundle:Mouvements:visualiser_mouvements_compte_mois.html.twig', array(
				'compte'					=> $compte[0],
				'id'						=> $id,
				'date'						=> array(
					'actuelle'		=> $date,
					'moins_1_mois'	=> $date_moins_1_mois,
					'plus_1_mois'	=> $date_plus_1_mois,
				),
				'totaux_par_categorie'		=> array_filter($montantCategorie,$callback_depense),
				'totaux'					=> array (
					'depense_planified'		=> isset($totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_DEPENSE]) ? $totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_DEPENSE] : 0,
					'revenu_planified'		=> isset($totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_REVENU]) ?$totalDepenseAndRevenuPlanified[CategorieMouvementFinancier::TYPE_REVENU] : 0,
					'depense_not_planified'	=> isset($totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_DEPENSE]) ? $totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_DEPENSE] : 0,
					'revenu_not_planified'	=> isset($totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_REVENU]) ?$totalDepenseAndRevenuNotPlanified[CategorieMouvementFinancier::TYPE_REVENU] : 0,
					'nb_planified'			=>	$nbPlanified,
					'nb_not_planified'		=> $nbNotPlanified,
				),
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
