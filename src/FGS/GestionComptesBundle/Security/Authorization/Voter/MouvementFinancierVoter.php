<?php 
namespace FGS\GestionComptesBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MouvementFinancierVoter extends Voter
{
	const PROPRIETAIRE 	= 'proprietaire';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
	 //équivalent de getSupportedAttributes et getSupportedClasses
    protected function supports($attribute, $subject)
    {
    	if ((($subject instanceof \FGS\GestionComptesBundle\Entity\MouvementFinancier) || ($subject instanceof \FGS\GestionComptesBundle\Entity\MouvementFinancierPlanifie)) 
    		&&   ($attribute == self::PROPRIETAIRE))  	
    		return true;
    	else
    		return false;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    //équivalent de isGranted
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
		switch ($attribute) {
			case (self::PROPRIETAIRE) : //on regarde si l'utilisateur est le propriétaire du compte ciblé par le mouvement financier
										if (($token->getUser() !== null) && ($token->getUser()->getId() === $subject->getCompte()->getUtilisateur()->getId())) {
											return true;
										}
										break;						
		}
		return false;
	}
}
