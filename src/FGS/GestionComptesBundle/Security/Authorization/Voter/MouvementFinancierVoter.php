<?php 
namespace FGS\GestionComptesBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use FGS\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class MouvementFinancierVoter extends AbstractVoter
{
	const PROPRIETAIRE 	= 'proprietaire';
	
	protected function getSupportedAttributes()
	{
		return Array(self::PROPRIETAIRE);
	}
	
	protected function getSupportedClasses() 
	{
		return Array(	"FGS\GestionComptesBundle\Entity\MouvementFinancier", 
						"FGS\GestionComptesBundle\Entity\MouvementFinancierPlanifie");
	}

	/**
	 * @param \FGS\GestionComptesBundle\Entity\MouvementFinancier $object
	 * @param \FGS\UserBundle\Entity\User $user
	 */
	public function isGranted($attribute, $object, $user = null)
	{
		if (!$user instanceof \Symfony\Component\Security\Core\User\UserInterface)
			return false;

		if (!$user instanceof \FGS\UserBundle\Entity\User)
				return false;
		
		switch ($attribute) {
			case (self::PROPRIETAIRE) : //on regarde si l'uilitsateur est le propriétaire du compte ciblé par le mouvement financier
										if ($user->getId() === $object->getCompte()->getUtilisateur()->getId()) {
											return true;
										}
										break;
		}
		return false;
	}
}
