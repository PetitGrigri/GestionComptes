<?php

namespace FGS\GestionComptesBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class MouvementFinancierPlanifieType extends AbstractType
{
    /**
     * MouvementFinancierPlanifieType constructor.
     * Les paramètres sont transmis par injection de dépendance
     * @param EntityManager $entityManager
     * @param TokenStorage $token
     */
    public function __construct(EntityManager $entityManager, TokenStorage $token)
    {
        $this->entityManager	= $entityManager;
        $this->utilisateurId    = $token->getToken()->getUser()->getId();
    }
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class)
            ->add('montant', MoneyType::class)
            ->add(	'dateInitiale', DateType::class, array(
            		'widget' => 'single_text',
            		'format' => 'dd-MM-yyyy',
	            	'attr'		=> array(
	            		'autocomplete'	=> 'off',
	            	),
            ))
            ->add('intervalType', ChoiceType::class, array(
            	'label'		=> 'Type de récurence',
            	'choices'	=>	array(
            		'WEEK'	=> 'Hebdomadaire',
            		'MONTH'	=> 'Mensuelle',
            	),
            ))
            ->add('intervalValeur', IntegerType::class, array(
            	'label'		=> 'Interval de la récurence',
            	
            ))
            ->add('compte', 	EntityType::class, 	array(
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
				$mfp	= $event->getData();
				$cmf	= $mfp->getCategorieMouvementFinancier();

				$form->add(	'categorieMouvementFinancier', EntityType::class, array(
						'class'			=>	'FGSGestionComptesBundle:CategorieMouvementFinancier',
						'choices'		=>	$this->entityManager
                                                ->getRepository('FGSGestionComptesBundle:CategorieMouvementFinancier')
                                                ->getFlatTreeCategories($this->utilisateurId, $cmf, true),
						'label'			=>	'Catégorie',
						'placeholder'	=>	'Aucune catégorie',
						'required'		=>	true,
				));
				
				if (!$mfp || $mfp->getId() === null)
				{
					if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE) {
						$form->add('sauver', SubmitType::class, array('label'=>'Ajouter cette dépense Planifié'));
					}
					else {
						$form->add('sauver', SubmitType::class, array('label'=>'Ajouter ce revenu planifié'));
					}
				}
				else
				{
					if ($mfp->getCategorieMouvementFinancier()->getType() == CategorieMouvementFinancier::TYPE_DEPENSE) {
						$form->add('sauver', SubmitType::class, array('label'=>'Modifier cette dépense planifié'));
					}
					else {
						$form->add('sauver', SubmitType::class, array('label'=>'Modifier ce revenu planifié'));
					}
				}
			});
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FGS\GestionComptesBundle\Entity\MouvementFinancierPlanifie'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'fgs_gestioncomptesbundle_mouvementfinancierplanifie';
    }
}
