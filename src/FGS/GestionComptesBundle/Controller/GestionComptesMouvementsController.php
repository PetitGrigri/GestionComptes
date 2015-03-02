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


class GestionComptesMouvementsController extends Controller
{
	public function ajouterDepenseAction(Request $request)
	{
		$mf		= new MouvementFinancier();
		$cmf	= new CategorieMouvementFinancier();

		
		
		$cmf_parent	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->findOneBy(array(
			'type'		=>	CategorieMouvementFinancier::TYPE_DEPENSE,
			'parent'	=>	null
		));
		
		$cmf->setType(CategorieMouvementFinancier::TYPE_DEPENSE);
		$cmf->setParent($cmf_parent);
		
		$mf->setCategorieMouvementFinancier($cmf);

		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine()), $mf);
		
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
			$em	=	$this->getDoctrine()->getManager();
			
			//il s'agit d'une dépense donc focément négative
			$mf->setMontant(-abs($mf->getMontant()));
			
			$em->persist($mf);
			$em->flush();
			
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La dépense a été prise en compte!');
			
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
    	return $this->render('FGSGestionComptesBundle:GestionComptes:ajouter_depense.html.twig', array(
  			'form'	=>	$form->createView(),
    	));
	}
	
	
	public function ajouterRevenuAction(Request $request)
	{
		$mf		= new MouvementFinancier();
		$cmf	= new CategorieMouvementFinancier();
	
	
	
		$cmf_parent	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')->findOneBy(array(
				'type'		=>	CategorieMouvementFinancier::TYPE_REVENU,
				'parent'	=>	null
		));
	
		$cmf->setType(CategorieMouvementFinancier::TYPE_REVENU);
		$cmf->setParent($cmf_parent);
	
		$mf->setCategorieMouvementFinancier($cmf);
	
		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine()), $mf);
	
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
			$em	=	$this->getDoctrine()->getManager();
				
			//il s'agit d'une dépense donc focément négative
			$mf->setMontant(abs($mf->getMontant()));
				
			$em->persist($mf);
			$em->flush();
				
			$session	=	new Session();
			$session->getFlashBag()->add('success', 'Le revenu a été prise en compte!');
				
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
		}
		return $this->render('FGSGestionComptesBundle:GestionComptes:ajouter_revenu.html.twig', array(
				'form'	=>	$form->createView(),
		));
	}

	
	public function supprimerMouvementFinancierAction($id)
	{
	
		$em					= $this->getDoctrine()->getManager();
	
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
		return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
	}
	
	public function modifierMouvementFinancierAction($id,Request $request)
	{
		//récupération du mouvement financier que l'on veut modifier, et sa catégorie
		$mf		= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:MouvementFinancier')->find($id);
		$cmf	= $mf->getCategorieMouvementFinancier();

		//mémorisation des informations avant l'update
		
		$form = $this->createForm(new MouvementFinancierType($this->getDoctrine()), $mf);
	
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
			$em	=	$this->getDoctrine()->getManager();
			
			if ($cmf->getType()== CategorieMouvementFinancier::TYPE_DEPENSE ) {
				$mf->setMontant(- abs($mf->getMontant()));
			}
			else {
				$mf->setMontant(abs($mf->getMontant()));
			}	
			
			$em->persist($mf);
			$em->flush();

			$session	=	new Session();
			$session->getFlashBag()->add('success', 'La modification de votre '.$cmf->getType().' a été modifié !');
				
			return $this->redirect($this->generateUrl("fgs_gestion_comptes_homepage"));
			
		}
		
		return $this->render('FGSGestionComptesBundle:GestionComptes:modifier_mouvement_financier.html.twig', array(
				'form'	=>	$form->createView(),
		));
	}
	
	
	
	public function voirMouvementFinancierCompteAction($id, $debut = 0, $longueur = 15)
	{
		
		$Max		= intval($this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte')->getCompteMaxMouvements($id));
		$compte		= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte')->getCompteMouvementAndCategorie($id, $debut, $longueur);
		
		return $this->render('FGSGestionComptesBundle:GestionComptes:visualiser_mouvements_compte.html.twig', array(
			'compte'				=> $compte[0],
			'maxMouvementFinanciers'=> $Max,
			'positionActuelle'		=> $debut,
			'longueur'				=> $longueur,
			'id'					=> $id,
		));
	}
	
	public function voirMouvementFinancierCompteMoisAction($id)
	{
		$date 		= new \DateTime("2015-02-05");
		$anneeMois	= $date->format('Y-m');
		$repository	= $this->getDoctrine()->getRepository('FGSGestionComptesBundle:Compte');
		
		$compte		= $repository->getCompteMouvementAndCategorieMois($id, $anneeMois);
		
		$montantCategorie	= $repository->getMontantForEachCategorie($id, $anneeMois);
		
		$totalDepenseAndRevenu	= $repository->getDepenseAndRevenu($id, $anneeMois);
		
		
		//\Doctrine\Common\Util\Debug::dump($totalDepenseAndRevenu);
		
		return $this->render('FGSGestionComptesBundle:GestionComptes:visualiser_mouvements_compte_mois.html.twig', array(
				'compte'				=> $compte[0],
				'id'					=> $id,
				'date'					=> $date,
				'totauxParCategorie'	=> $montantCategorie,
				'totalDepense'			=> $totalDepenseAndRevenu[CategorieMouvementFinancier::TYPE_DEPENSE],
				'totalRevenu'			=> $totalDepenseAndRevenu[CategorieMouvementFinancier::TYPE_REVENU],
		));
	}
}
