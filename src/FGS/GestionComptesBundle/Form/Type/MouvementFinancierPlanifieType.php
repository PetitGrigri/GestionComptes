<?php

namespace FGS\GestionComptesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;

class MouvementFinancierPlanifieType extends AbstractType
{
	public function __construct(RegistryInterface $doctrine, $utilisateurId)
	{
		$this->doctrine				= $doctrine;
		$this->utilisateurId		= $utilisateurId;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', 'text')
            ->add('montant', 'money')
            ->add(	'dateInitiale', 'date', array(
            		'widget' => 'single_text',
            		'format' => 'dd-MM-yyyy',
            ))
            ->add('intervalType', 'choice', array(
            	'label'		=> 'Type de récurence',
            	'choices'	=>	array(
            		'WEEK'		=> 'Hebdomadaire',
            		'MONTH'		=> 'Mensuelle',
            		
            	),
            ))
            ->add('intervalValeur', 'integer', array(
            	'label'		=> 'Interval de la récurence',
            	
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
				$mfp	= $event->getData();
				$cmf	= $mfp->getCategorieMouvementFinancier();

				$form->add(	'categorieMouvementFinancier', 'entity', array(
						'class'			=>	'FGSGestionComptesBundle:CategorieMouvementFinancier',
						'choices'		=>	$this->doctrine->getManager()
												->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
												->getFlatTreeCategories($this->utilisateurId, $cmf, true),
						'label'			=>	'Catégorie',
						'empty_value'	=>	'Aucune catégorie',
						'required'		=>	true,
				));
				
				if (!$mfp || $mfp->getId() === null)
				{
					if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE) {
						$form->add('sauver', 'submit', array('label'=>'Ajouter cette dépense Planifié'));
					}
					else {
						$form->add('sauver', 'submit', array('label'=>'Ajouter ce revenu planifié'));
					}
				}
				else
				{
					if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE) {
						$form->add('sauver', 'submit', array('label'=>'Modifier cette dépense planifié'));
					}
					else {
						$form->add('sauver', 'submit', array('label'=>'Modifier ce revenu planifié'));
					}
				}
				//mis dans le gestionnaire d'évènement après l'ajout du bouton de sauvegarde afin un bug d'afichage
				//$form->add('effacer','reset');
			});
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FGS\GestionComptesBundle\Entity\MouvementFinancierPlanifie'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fgs_gestioncomptesbundle_mouvementfinancierplanifie';
    }
}
