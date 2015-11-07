<?php
namespace FGS\UserBundle\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Doctrine\ORM\EntityManager;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;


class NewUtilisateurListener implements EventSubscriberInterface
{
	private $_em;
	
	public function __construct(EntityManager $em)
	{
		$this->_em 							= $em;
	}
	
	public static function getSubscribedEvents()
	{
		return array(
				FOSUserEvents::REGISTRATION_COMPLETED => 'processNewUtilisateur',
		);
	}
	
	public function processNewUtilisateur(FilterUserResponseEvent $responseEvent)
	{
		$user 		= $responseEvent->getUser();
		$depense	= new CategorieMouvementFinancier();
		$revenu		= new CategorieMouvementFinancier();
		
		$depense
			->setType(CategorieMouvementFinancier::TYPE_DEPENSE)
			->setLibelle('Dépense')
			->setIcone('icon-arrow-graph-down-right')
			->setOrdre('1')
			->setUtilisateur($user);
		
		$revenu
			->setType(CategorieMouvementFinancier::TYPE_REVENU)
			->setLibelle('Revenu')
			->setIcone('icon-arrow-graph-up-right')
			->setOrdre('2')
			->setUtilisateur($user);
		
		$this->_em->persist($depense);
		$this->_em->flush();
		
		$this->_em->persist($revenu);
		$this->_em->flush();
	}
}
