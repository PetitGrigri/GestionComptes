<?php

namespace FGS\GestionComptesBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class MouvementFinancierType extends AbstractType
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
		$this->utilisateurId    = $token->getToken()->getUser()->getId();
	}

	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		
		$builder
			->add(  'libelle', TextType::class, array())
			->add(  'montant',  MoneyType::class)
			->add(	'date', DateType::class, array(
					'widget'	=> 'single_text',
    				'format'	=> 'dd-MM-yyyy',
					'attr'		=> array(
						'autocomplete'	=> 'off',
					), 
			))
			->add(  'compte', 	EntityType::class, 	array(
					'class'			=>	'FGSGestionComptesBundle:Compte',
					'choices'		=>	$this->entityManager
											->getRepository('FGSGestionComptesBundle:Compte')
											->getComptesForUtilisateur($this->utilisateurId),
					'placeholder'	=>	'Selectionnez le compte cible',
			))
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
			{
				//récupération du formulaire en cours de création et de l'objet à hydrater
				$form 	= $event->getForm();
				$mf		= $event->getData();
				$cmf	= $mf->getCategorieMouvementFinancier();

				$form->add(	'categorieMouvementFinancier', EntityType::class, array(
						'class'			=>	'FGSGestionComptesBundle:CategorieMouvementFinancier',
						'choices'		=>	$this->entityManager
												->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
												->getFlatTreeCategories($this->utilisateurId, $cmf, true),
						'label'			=>	'Catégorie',
						'placeholder'	=>	'Aucune catégorie',
						'required'		=>	true,
								));
				
				$form->add(	'commentaire', TextareaType::class, array(
							'required'	=> false,
				));
				
				if (!$mf || $mf->getId() === null)
				{
					if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE)
					{
						$form->add('sauver', SubmitType::class, array('label'=>'Ajouter cette dépense'));
					}
					else
					{
						$form->add('sauver', SubmitType::class, array('label'=>'Ajouter ce revenu'));
					}
				}
				else
				{
					if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE)
					{
						$form->add('sauver', SubmitType::class, array('label'=>'Modifier cette dépense'));
					}
					else
					{
						$form->add('sauver', SubmitType::class, array('label'=>'Modifier ce revenu'));
					}
				}
			})
			;

	}


    public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setDefaults(array(
				'data_class' => 'FGS\GestionComptesBundle\Entity\MouvementFinancier'
		));
	}
	
	public function getBlockPrefix() {
		return "fgs_gestioncomptesbundle_mouvement_financier_type";
	}
}
