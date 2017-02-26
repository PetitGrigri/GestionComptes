<?php

namespace FGS\GestionComptesBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FGS\BootstrapBundle\Form\Type\ButtonTextType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CategorieMouvementFinancierType extends AbstractType
{
    /**
     * CategorieMouvementFinancierType constructor.
     * Les paramètres sont transmis par injection de dépendance
     * @param EntityManager $entityManager
     * @param TokenStorage $token
     */
	public function __construct(EntityManager $entityManager, TokenStorage $token)
	{
		$this->entityManager	= $entityManager;
        $this->token            = $token;
	}

	/*
	 * Constructeur du formulaire de type CatégorieMouvementFinancier
	 *
	 * Règle pour la modification d'une catégorie :
	 * o Une catégorie "Racine" (sans parent : Revenu ou Dépense) :
	 *     - ne peut pas avoir un parent
	 *     - ne peut pas avoir son libelle modifié
	 *
	 *
	 *
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
			{
				//récupération du formulaire en cours de création et de l'objet à hydrater
				$form 	= $event->getForm();
				$cmf	= $event->getData();
				
				//Si on a une entité "Racine", on empêche la modification du libellé
				if ($cmf && $cmf->getId() && $cmf->getParent() === null)
				{
					$form->add('libelle', TextType::class, array(	'label'		=> 'Nom de la catégorie',
															        'disabled'	=> true,
					));
				}
				else
				{
					$form->add('libelle', TextType::class, array(	'label'=>'Nom de la catégorie'));
				}
			})
			->add('icone', ButtonTextType::class)
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
			{
				//récupération du formulaire en cours de création et de l'objet à hydrater 
				$form 	= $event->getForm();
				$cmf	= $event->getData();
				

				//si on a une création, ou une modification d'une catégorie "non Racine" on ajoute le choix du parent
				if ((!$cmf || $cmf->getId() === null) || ($cmf && $cmf->getId() && $cmf->getParent() !== null))
				{
					$form->add(	'parent', EntityType::class, array(
								'class'			=>	'FGSGestionComptesBundle:CategorieMouvementFinancier',
								'choices'		=>	$this->entityManager
                                                        ->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
                                                        ->getFlatTreeCategories($this->token->getToken()->getUser()->getId(), $cmf),
								'label'			=>	'Catégorie parente',
								'placeholder'	=>	'Aucune catégorie parente',
								'required'		=>	true,			
								));
				}
				//si on a une création on ajoute le bouton submit "Ajouter" sinon on ajoute un un bouton submit "Modifier"
				if (!$cmf || $cmf->getId() === null)
				{
					$form->add('sauver', SubmitType::class, array('label'=>'Ajouter cette categorie'));
				}
				else
				{
					$form->add('sauver', SubmitType::class, array('label'=>'Modifier cette categorie'));
				}
			})
			;

	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => 'FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier',
        ]);
    }
	
	public function getBlockPrefix() {
		return "fgs_gestioncomptesbundle_categorie_mouvement_financier_type";
	}
}
