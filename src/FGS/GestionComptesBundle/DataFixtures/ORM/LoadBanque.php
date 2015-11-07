<?php
namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FGS\GestionComptesBundle\Entity\Banque;

class LoadBanque implements FixtureInterface
{
	// Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
	public function load(ObjectManager $manager)
	{
	 
		//création de la liste des différentes banques et de leurs images
		//source de la liste de ces  banques : http://lesmeilleuresbanques.com/banque-france
		$listeBanque = array(
			"Axa banque"							=>	"img/axa.png",
			"AGF banque"							=>	"",
			"Allianz banque"						=>	"",
			"Banque CIC"							=>	"img/cic.png",
			"Banque de Bretagne"					=>	"",
			"Banque de la Réunion"					=>	"",
			"Banque de Nouvelle Calédonie"			=>	"",
			"Banque de Polynésie"					=>	"",
			"Banque de Savoie"						=>	"",
			"Banque de Wallis et Futuna"			=>	"",
			"Banque d’Orsay"						=>	"",
			"Banque du crédit mutuel Ile-de-France – BCMI"		=>	"",
			"Banque Kolb"										=>	"",
			"Banque populaire Atlantique"						=>	"img/banque_populaire.png",
			"Banque populaire Bourgogne Franche-Comté"			=>	"img/banque_populaire.png",
			"Banque populaire Centre Atlantique"	=>	"img/banque_populaire.png",
			"Banque populaire Côte d’Azur"			=>	"img/banque_populaire.png",
			"Banque populaire d’Alsace"				=>	"img/banque_populaire.png",
			"Banque populaire de l’Ouest"			=>	"img/banque_populaire.png",
			"Banque populaire des Alpes"			=>	"img/banque_populaire.png",
			"Banque populaire du Massif central"	=>	"img/banque_populaire.png",
			"Banque populaire du Nord"				=>	"img/banque_populaire.png",
			"Banque populaire du Sud"				=>	"img/banque_populaire.png",
			"Banque populaire du Sud-Ouest"			=>	"img/banque_populaire.png",
			"Banque populaire Loire et Lyonnais"	=>	"img/banque_populaire.png",
			"Banque populaire Lorraine Champagne"	=>	"img/banque_populaire.png",
			"Banque populaire Occitane"				=>	"img/banque_populaire.png",
			"Banque populaire provençale et Corse"	=>	"img/banque_populaire.png",
			"Banque populaire Rives de Paris"		=>	"img/banque_populaire.png",
			"Banque populaire Val de France"		=>	"img/banque_populaire.png",
			"BPCE"									=>	"",
			"BNP Paribas"							=>	"",
			"Bonnasse – Lyonnaise de banque"		=>	"",
			"Boursorama banque"						=>	"",
			"Caisse agricole Crédit Mutuel"			=>	"",
			"Caisse de Bretagne de crédit agricole mutuel"						=>	"",
			"Caisse d’Epargne"													=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Aquitaine Poitou-Charentes"		=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Bretagne-Pays de Loire"			=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Côte d’Azur"						=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance d’Alsace"						=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance d’Auvergne et du Limousin"		=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance de Basse-Normandie"				=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance de Bourgogne Franche-Comté"		=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance de Haute-Normandie"				=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance de Lorraine Champagne-Ardenne"	=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance de Midi-Pyrénées"				=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance de Nouvelle-Calédonie"			=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance de Picardie"						=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance du Languedoc Roussillon"			=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Ile-de-France"					=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Loire Drôme Ardèche"				=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Loire-Centre"					=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Nord France Europe"				=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Provence-Alpes-Corse"			=>	"img/caisse_epargne.png",
			"Caisse d’épargne et de prévoyance Rhône Alpes"						=>	"img/caisse_epargne.png",
			"Caisse fédérale de crédit mutuel de Normandie"						=>	"",
			"Caisse fédérale de crédit mutuel du Centre"						=>	"",
			"Caisse fédérale du crédit mutuel agricole et rural Provence-Languedoc"		=>	"",
			"Caisse fédérale du crédit mutuel Antilles-Guyane"							=>	"",
			"Caisse fédérale du crédit mutuel Centre Est Europe"						=>	"",
			"Caisse fédérale du crédit mutuel d’Anjou"									=>	"",
			"Caisse fédérale du crédit mutuel de Maine-Anjou et Basse-Normandie"		=>	"",
			"Caisse fédérale du crédit mutuel Midi-Atlantique"					=>	"",
			"Caisse fédérale du crédit mutuel Nord Europe"						=>	"",
			"Caisse fédérale du crédit mutuel Océan"							=>	"",
			"Cofidis"					=>	"",
			"Crédit Agricole S.A."		=>	"",
			"Crédit du Nord"			=>	"",
			"Crédit Lyonnais"			=>	"img/credit_lyonnais.png",
			"Finaref"					=>	"",
			"Fortis banque France"		=>	"",
			"Fortuneo banque"			=>	"",
			"Franfinance"				=>	"",
			"Groupama banque"			=>	"",
			"HSBC France"				=>	"",
			"ING Direct"				=>	"",
			"LCL"						=>	"",
			"La Banque Postale"			=>	"img/banque_postale.png",
			"Médiatis"					=>	"",
			"Monabanq"					=>	"",
			"Natixis"					=>	"",
			"Rothschild et compagnie banque"	=>	"",
			"Société Générale"			=>	"img/societe_generale.png",
			"SNVB"						=>	"",
			"Sofinco"					=>	"img/sofinco.png",
		);

		//création des différents objets banques, remplissage et persistance
		foreach ($listeBanque as $nom => $urlImage) 
		{
			$banque	=	new Banque();
			$banque 
				->setNom($nom)
				->setUrlImage($urlImage);

			$manager->persist($banque);
		}

		// Enregistrement en base de donnée
		$manager->flush();
	}
}
