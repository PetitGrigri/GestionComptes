<?php
// src/OC/PlatformBundle/DataFixtures/ORM/LoadCategory.php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FGS\GestionComptesBundle\Entity\TypeCompte;

class LoadTypeCompte implements FixtureInterface
{
	// Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
	public function load(ObjectManager $manager)
	{
	 
		//création des différents types de compte
		$listeTypeCompte = array(
			"Compte chèque"		=>	"Compte chèque",
			"Compte Courant"	=>	"Compte courant",
			"Livret A" 			=>	"Livre A",
			"LDD"				=>	"Livret de développement durable",
			"CEL"				=>	"Compte d'épargne logement",
			"LEP"				=>	"Livret d'epargne populaire",
			"Livre Jeune"		=>	"Livret jeune",
	 		"Compte à Terme"	=>	"Compte à terme",
	 		"Compte à préavis"	=>	"Compte à préavis",
	 		"Plan d'epargne"	=>	"Plan d'épargne",
	 		"PEL"				=>	"Plan d'épargne logement",
			"PEA"				=>	"Plan d'épargne actions",
			"PERP"				=>	"Plan d'épargne retraite"
		);

	 	//création des différents objets typeCompte, remplissage de ces derniers et persistance
		foreach ($listeTypeCompte as $libelleCourt => $libelleLong) 
		{
			$typeCompte	=	new TypeCompte();
			
			$typeCompte
				->setLibelleCourt($libelleCourt)
				->setLibelleLong($libelleLong);
			
			$manager->persist($typeCompte);
		}

		// Enregistrement en base de donnée
		$manager->flush();
	}
}
