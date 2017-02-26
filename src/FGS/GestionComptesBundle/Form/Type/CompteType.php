<?php

namespace FGS\GestionComptesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class CompteType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder
			->add('nom',			TextType::class)
			->add('montantActuel', 	MoneyType::class)
			
			->add('typeCompte', 	'entity', 	array(
				'class'			=>	'FGSGestionComptesBundle:TypeCompte',
				'property'		=>	'libelleLong',
				'empty_value'	=>	'Selectionnez un type de compte',
			))
			->add('banque', 		'entity', 	array(
				'class'		=>	'FGSGestionComptesBundle:Banque',
				'property'	=>	'nom',
				'empty_value'	=>	'Selectionnez une banque',
			))
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
			{	
				$form = $event->getForm();
				$compte	= $event->getData();
				
				if (null === $compte->getId())
				{
					$form->add('sauver', SubmitType::class, array('label'=>'Ajouter ce compte'));
				}
				else {
					$form->add('sauver', SubmitType::class, array('label'=>'Modifier ce compte'));
				}
			});
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'FGS\GestionComptesBundle\Entity\Compte'
		));
	}
	
	public function getName() {
		return "fgs_gestioncomptesbundle_compte_type";
	}
}
