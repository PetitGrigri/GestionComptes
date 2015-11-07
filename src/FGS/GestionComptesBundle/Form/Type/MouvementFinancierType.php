<?php

namespace FGS\GestionComptesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FGS\GestionComptesBundle\Entity\Compte;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use FGS\BootstrapBundle\Form\Type\ButtonTextType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;

class MouvementFinancierType extends AbstractType
{
	/* (non-PHPdoc)
	 * @see \Symfony\Component\Form\AbstractType::buildForm()
	 */
	public function __construct(RegistryInterface $doctrine, $utilisateurId)
	{
		$this->doctrine				= $doctrine;
		$this->utilisateurId		= $utilisateurId;
	}

	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		
		$builder
			->add('libelle', 'text', array())
			->add('montant',  'money')
			->add(	'date', 'date', array(
					'widget' => 'single_text',
    				'format' => 'dd-MM-yyyy',
			))
			->add('compte', 	'entity', 	array(
					'class'			=>	'FGSGestionComptesBundle:Compte',
					'choices'		=>	$this->doctrine->getManager()
											->getRepository('FGSGestionComptesBundle:Compte')
											->getComptesForUtilisateur($this->utilisateurId),
					'empty_value'	=>	'Selectionnez le compte cible',
			))
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
			{
				//récupération du formulaire en cours de création et de l'objet à hydrater
				$form 	= $event->getForm();
				$mf		= $event->getData();
				$cmf	= $mf->getCategorieMouvementFinancier();

				$form->add(	'categorieMouvementFinancier', 'entity', array(
						'class'			=>	'FGSGestionComptesBundle:CategorieMouvementFinancier',
						'choices'		=>	$this->doctrine->getManager()
												->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
												->getFlatTreeCategories($this->utilisateurId, $cmf, true),
						'label'			=>	'Catégorie',
						'empty_value'	=>	'Aucune catégorie',
						'required'		=>	true,
								));
				
				$form->add(	'commentaire', 'textarea', array(
							'required'	=> false,
				));
				
				if (!$mf || $mf->getId() === null)
				{
					if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE)
					{
						$form->add('sauver', 'submit', array('label'=>'Ajouter cette dépense'));
					}
					else
					{
						$form->add('sauver', 'submit', array('label'=>'Ajouter ce revenu'));
					}
				}
				else
				{
					if ($mf->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE)
					{
						$form->add('sauver', 'submit', array('label'=>'Modifier cette dépense'));
					}
					else
					{
						$form->add('sauver', 'submit', array('label'=>'Modifier ce revenu'));
					}
				}
				//mis dans le gestionnaire d'évènement après l'ajout du bouton de sauvegarde afin un bug d'afichage
				$form->add('effacer','reset');
			})
			;

	}
	
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'FGS\GestionComptesBundle\Entity\MouvementFinancier'
		));
	}
	
	public function getName() {
		return "fgs_gestioncomptesbundle_mouvement_financier_type";
	}
}
