<?php

namespace FGS\GestionComptesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FGS\GestionComptesBundle\Entity\Compte;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CompteType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder
			->add('nom',			'text' )
			->add('montantActuel', 	'money')
			
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
			;
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
