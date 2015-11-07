<?php
namespace FGS\UserBundle\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UtilisateurResettingListener implements EventSubscriberInterface
{
	private $_router;
	
	public function __construct(UrlGeneratorInterface $router)
	{
		$this->_router							= $router;

	}
	
	public static function getSubscribedEvents()
	{
		return array(
				FOSUserEvents::RESETTING_RESET_SUCCESS => 'processNewUtilisateur',
		);
	}
	
	public function processNewUtilisateur(FormEvent $event)
	{
		$event->setResponse(new RedirectResponse($this->_router->generate('fgs_gestion_comptes_homepage')));
	}
}
