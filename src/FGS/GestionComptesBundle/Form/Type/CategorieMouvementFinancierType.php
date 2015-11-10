<?php

namespace FGS\GestionComptesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use FGS\BootstrapBundle\Form\Type\ButtonTextType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class CategorieMouvementFinancierType extends AbstractType
{
	/* (non-PHPdoc)
	 * @see \Symfony\Component\Form\AbstractType::buildForm()
	 */
	public function __construct(RegistryInterface $doctrine, $utilisateurId)
	{
		$this->doctrine			= $doctrine;
		$this->utilisateurId	= $utilisateurId;	
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
					$form->add('libelle', 'text', array(	'label'		=> 'Nom de la catégorie',
															'disabled'	=> true,					
					));
				}
				else
				{
					$form->add('libelle', 'text', array(	'label'=>'Nom de la catégorie'));
				}
			})
			->add('icone', new ButtonTextType())
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
			{
				//récupération du formulaire en cours de création et de l'objet à hydrater 
				$form 	= $event->getForm();
				$cmf	= $event->getData();
				

				//si on a une création, ou une modification d'une catégorie "non Racine" on ajoute le choix du parent
				if ((!$cmf || $cmf->getId() === null) || ($cmf && $cmf->getId() && $cmf->getParent() !== null))
				{
					$form->add(	'parent', 'entity', array(
								'class'			=>	'FGSGestionComptesBundle:CategorieMouvementFinancier',
								'choices'		=>	$this->doctrine->getManager()
									->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
									->getFlatTreeCategories($this->utilisateurId, $cmf),
								'label'			=>	'Catégorie parente',
								'empty_value'	=>	'Aucune catégorie parente',
								'required'		=>	true,			
								));
				}
				//si on a une création on ajoute le bouton submit "Ajouter" sinon on ajoute un un bouton submit "Modifier"
				if (!$cmf || $cmf->getId() === null)
				{
					$form->add('sauver', 'submit', array('label'=>'Ajouter cette categorie'));
				}
				else
				{
					$form->add('sauver', 'submit', array('label'=>'Modifier cette categorie'));
				}
			})
			;

	}
	
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier'
		));
	}
	
	public function getName() {
		return "fgs_gestioncomptesbundle_categorie_mouvement_financier_type";
	}
}
