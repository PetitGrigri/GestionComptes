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
use FGS\GestionComptesBundle\Entity\MouvementFinancier;
use FGS\GestionComptesBundle\Form\MouvementFinancierType;
use FGS\GestionComptesBundle\Entity\MouvementFinancierPlanifie;
use FGS\GestionComptesBundle\Form\MouvementFinancierPlanifieType;


class MouvementsController extends Controller
{
	public function ajouterDepenseAction(Request $request)
	{
		$mf				= new MouvementFinancier();
		$utilisateur	= $this->getUser();

		$cmf	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->findOneBy(array(
			'type'		=>	CategorieMouvementFinancier::TYPE_DEPENSE,
			'parent'	=>	null,
			'utilisateur'	=>	$utilisateur
		));

		$mf->setCategorieMouvementFinancier($cmf);

		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine(), $utilisateur->getId()), $mf);
		
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
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
	
		
		if ($form->isValid())
		{
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
		
		if ($mf != null)
		{
			$em->remove($mf);
		
			$em->flush();
		
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'Votre '.$mf->getCategorieMouvementFinancier()->getType().' a été supprimé !');
		}
		else
		{
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
		
		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine(), $user->getId()), $mf);
	
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
			$this->checkCoherenceMouvementFinancier($mf);
			$em->flush();
			//\Doctrine\Common\Util\Debug::dump($mf);
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
		$date		= (($annee != null)&&($mois!=null))? new \DateTime("$annee-$mois"):new \DateTime("now");
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
		$date		= ($annee != null)? new \DateTime("$annee-01"):new \DateTime("now");

		$data_annee_mois	= array();
		
		for ($compteurMois =1; $compteurMois<=12; $compteurMois++)
		{
			$data_annee_mois[$date->format('Y').'-'.str_pad($compteurMois,2,'0',STR_PAD_LEFT)]	=	array(
				"montant_depense" => 0,
				"montant_revenu" => 0,
				"date" => new \DateTime($date->format('Y').'-'.$compteurMois)
			);
		}

		$repository	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte');

		$BilanAnnuelDepensesRevenu		= $repository->getDepenseAndRevenuByTypeForYear($id, $date->format('Y'));
		$compte							= $repository->find($id);
		
		foreach ($BilanAnnuelDepensesRevenu as $data)
		{
			if ($data["type"] == CategorieMouvementFinancier::TYPE_DEPENSE)
			{
				$data_annee_mois[$data["annee_mois"]]["montant_depense"] = $data["total"];
			}
			else 
			{
				$data_annee_mois[$data["annee_mois"]]["montant_revenu"] = $data["total"];
			}
			//\Doctrine\Common\Util\Debug::dump($data);
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
		
		//la recherche via repository est moins rapide que l'utilisation de find... incompréhensible :(
		//$mf 	= $em->getRepository('FGSGestionComptesBundle:MouvementFinancier')->getMouvementFinancierAndCompte($id);
		$mf 	= $em->getRepository('FGSGestionComptesBundle:MouvementFinancier')->find($id);
		
		if ($mf->getCompte()->getUtilisateur()->getId() != $this->getUser()->getId())
		{
			$session	= $this->getRequest()->getSession();
			$session->getFlashBag()->add('error', 'Vous ne pouvez pas checker ce mouvement financier !');
		}
		else
		{
			$mf->setCheckBanque(($mf->getCheckBanque()) ? false : true);
			$em->flush();
		}

		return $this->redirect($this->getRequest()->headers->get('referer'));
	}
	
	public function ajouterDepensePLanifieeAction(Request $request)
	{
		$mfp			= new MouvementFinancierPlanifie();
		$utilisateur	= $this->getUser();
		$today			= new \DateTime('today');
		
		$cmf	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->findOneBy(array(
				'type'			=>	CategorieMouvementFinancier::TYPE_DEPENSE,
				'parent'		=>	null,
				'utilisateur'	=>	$utilisateur
		));
		
		$mfp->setCategorieMouvementFinancier($cmf);
		
		$form = $this->createForm(new MouvementFinancierPlanifieType($this->getDoctrine(), $utilisateur->getId()), $mfp);
		
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
			$em	=	$this->getDoctrine()->getManager();
			
			$this->checkCoherenceMouvementFinancierPlanifie($mfp);

			//dans le cas ou le mouvement financier planifié commence le jour même on crée aussi le mouvement financier
			if ($mfp->getDateInitiale() == $today)
			{
				$mf	= $this->createMouvementFinancierFromMouvementFinanciePlanifie($mfp);
				$em->persist($mf);
			}
			
			$em->persist($mfp);
			$em->flush();

			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La dépense planifié a été prise en compte!');
		
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
		
		return $this->render('FGSGestionComptesBundle:Mouvements:ajouter_depense_planifiee.html.twig', array(
				'form'	=>	$form->createView(),
		));
		
		return $this->render('FGSGestionComptesBundle:Mouvements:ajouter_depense_planifiee.html.twig');
	}
	
	public function ajouterRevenuPLanifieAction(Request $request)
	{
		$mfp			= new MouvementFinancierPlanifie();
		$utilisateur	= $this->getUser();
		$today			= new \DateTime('today');
		
		$cmf	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->findOneBy(array(
				'type'			=>	CategorieMouvementFinancier::TYPE_REVENU,
				'parent'		=>	null,
				'utilisateur'	=>	$utilisateur
		));

		$mfp->setCategorieMouvementFinancier($cmf);
		
		$form = $this->createForm(new MouvementFinancierPlanifieType($this->getDoctrine(), $utilisateur->getId()), $mfp);
		
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
			$em	=	$this->getDoctrine()->getManager();
			
			$this->checkCoherenceMouvementFinancierPlanifie($mfp);

			//dans le cas ou le mouvement financier planifié commence le jour même on crée aussi le mouvement financier
			if ($mfp->getDateInitiale() == $today)
			{
				$mf	= $this->createMouvementFinancierFromMouvementFinanciePlanifie($mfp);
				$em->persist($mf);
			}
			
			$em->persist($mfp);
			$em->flush();

			$session	=	new Session();
			$session->getFlashBag()->add('success', 'Le revenu planifié a été prise en compte!');
		
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
		
		return $this->render('FGSGestionComptesBundle:Mouvements:ajouter_revenu_planifie.html.twig', array(
				'form'	=>	$form->createView(),
		));
		
		return $this->render('FGSGestionComptesBundle:Mouvements:ajouter_revenu_planifie.html.twig');
	}
	
	public function voirMouvementFinancierPlanifieAction()
	{
		$utilisateur	= $this->getUser();

		$liste_mouvements_planifies	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:MouvementFinancierPlanifie')
			->getMouvementsFinanciersPlanifiesForUtilisateur($utilisateur->getId());
			
			
			
		return $this->render('FGSGestionComptesBundle:Mouvements:visualiser_mouvements_planifies.html.twig', array(
				'liste_mouvements_financiers_planifies'	=>	$liste_mouvements_planifies,
		));
	}
	
	public function modifierMouvementFinancierPlanifieAction($id,Request $request)
	{

		$utilisateur	= $this->getUser();
		$today			= new \DateTime('today');
		
		$mfp	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:MouvementFinancierPlanifie')->find($id);
		
		$form = $this->createForm(new MouvementFinancierPlanifieType($this->getDoctrine(), $utilisateur->getId()), $mfp);
		
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
			$em	=	$this->getDoctrine()->getManager();
				
			$this->checkCoherenceMouvementFinancierPlanifie($mfp);
		
			//dans le cas ou le mouvement financier planifié commence le jour même on crée aussi le mouvement financier
			if ($mfp->getDateInitiale() == $today)
			{
				$mf	= $this->createMouvementFinancierFromMouvementFinanciePlanifie($mfp);
				$em->persist($mf);
			}
				
			$em->persist($mfp);
			$em->flush();
		
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La dépense planifié a été modifiée !');
		
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_voir_mouvements_planifies"));
		}

		return $this->render('FGSGestionComptesBundle:Mouvements:modifier_mouvement_financier_planifie.html.twig', array(
				'form'	=> $form->createView(),
				'type' 	=> $mfp->getCategorieMouvementFinancier()->getType(),
		));
	}
	
	
	public function supprimerMouvementFinancierPlanifieAction($id)
	{
		$em		= $this->getDoctrine()->getManager();
	
		$mfp = $em->find('FGSGestionComptesBundle:MouvementFinancierPlanifie', $id);
	
		if ($mfp != null)
		{
			$em->remove($mfp);
	
			$em->flush();
	
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'Votre '.$mfp->getCategorieMouvementFinancier()->getType().' a été supprimé !');
		}
		else
		{
			$session	=	new Session();
			$session->getFlashBag()->add('error', 'TODO.');
		}
		return $this->redirect($this->getRequest()->headers->get('referer'));
	}
	
	
	private function checkCoherenceMouvementFinancier(MouvementFinancier $mf)
	{
		$today		= new \DateTime('today');
		
		//vérification de la date pour savoir si le mouvement financier sera planifié ou pas
		if ($mf->getDate() > $today)
		{
			$mf->setIsPlanified(true);
		}
		else
		{
			$mf->setIsPlanified(false);
		}
		
		//vérification du type de la categorie du mouvement financier pour avoir un montant cohérent
		if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE)
		{
			$mf->setMontant(-abs($mf->getMontant()));
		}
		if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_REVENU)
		{
			$mf->setMontant(abs($mf->getMontant()));
		}
	}
	
	private function checkCoherenceMouvementFinancierPlanifie(MouvementFinancierPlanifie $mfp)
	{
		//vérification du type de la categorie du mouvement financier pour avoir un montant cohérent
		if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE)
		{
			$mfp->setMontant(-abs($mfp->getMontant()));
		}
		if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_REVENU)
		{
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
		$mf->setIsPlanified(false);
		
		return $mf;
	}
}