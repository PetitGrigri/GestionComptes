<?php

namespace FGS\GestionComptesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * MouvementFinancier
 *
 * @ORM\Table(name="mouvement_financier")
 * @ORM\Entity(repositoryClass="FGS\GestionComptesBundle\Entity\MouvementFinancierRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MouvementFinancier
{
	public function __construct()
	{
		//initialisation dans le constructeur de certaines valeurs par défaut
		$this->checkBanque	= false;
		$this->isPlanified 	= false;
		$this->wasPlanified = false;
		
		//paramètres à mémoriser dans le cas d'un update ou d'un delete
		$this->oldCompte		= null;
		$this->oldMontant		= null;
		$this->oldIsPlanified	= null;
	}
	
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="montant", type="decimal", precision=20, scale=2)
     */
    private $montant;
    /**
     * 
     * @ORM\Column(name="date", type="date")
     */
    private $date;
	/**
	 * @var String
	 * 
	 * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
	 */
    private $commentaire;
    
	/**
	 * @ORM\ManyToOne(targetEntity="FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier")
     * @ORM\JoinColumn(name="categorie_mouvement_financier_id",referencedColumnName="id", nullable=true)
	 * @var unknown
	 */
    private $categorieMouvementFinancier;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="FGS\GestionComptesBundle\Entity\Compte", inversedBy="mouvementFinanciers" )
     * @ORM\JoinColumn(name="compte_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $compte;
    
    /**
     * @ORM\Column(name="check_banque", type="boolean")
     * 
     */
    private $checkBanque;
    
    /**
     * @ORM\Column(name="is_planified", type="boolean", nullable=false)
     * @var boolean
     */
    private $isPlanified;
    
    /**
     * @ORM\Column(name="was_planified", type="boolean", nullable=false)
     * @var boolean
     */
    private $wasPlanified;
    
    
    private $oldMontant;
    private $oldCompte;
    private $oldIsPlanified;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return MouvementFinancier
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set montant
     *
     * @param string $montant
     * @return MouvementFinancier
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return string 
     */
    public function getMontant()
    {
        return $this->montant;
    }
	public function getDate() {
		return $this->date;
	}
	public function setDate($date) {
		$this->date = $date;
		return $this;
	}
	public function getCommentaire() {
		return $this->commentaire;
	}
	public function setCommentaire($commentaire) {
		$this->commentaire = $commentaire;
		return $this;
	}
	public function getCategorieMouvementFinancier() {
		return $this->categorieMouvementFinancier;
	}
	public function setCategorieMouvementFinancier($categorieMouvementFinancier) {
		$this->categorieMouvementFinancier = $categorieMouvementFinancier;
		return $this;
	}
	/**
	 * @return Compte
	 */
	public function getCompte() {
		return $this->compte;
	}
	public function setCompte($compte) {
		$this->compte = $compte;
		return $this;
	}
	public function getCheckBanque() {
		return $this->checkBanque;
	}
	public function setCheckBanque($checkBanque) {
		$this->checkBanque = $checkBanque;
		return $this;
	}

	public function isPlanified() {
		return $this->isPlanified;
	}
	
	public function wasPlanified() {
		return $this->wasPlanified;
	}
	public function setPlanified($isPlanified) {
		$this->isPlanified = $isPlanified;
		return $this;
	}
	public function setWasPlanified($wasPlanified) {
		$this->wasPlanified = $wasPlanified;
		return $this;
	}
	
	
	/**
	 * @ORM\PostPersist
	 */
	public function changeMontantActuelCompteInsert(LifecycleEventArgs $args)
	{
		if (!$this->isPlanified)
		{
			$em	=	$args->getEntityManager();
			$compte = $this->getCompte();
			
			$compte->setMontantActuel($this->getCompte()->getMontantActuel() + $this->getMontant());
			
			$em->flush();
		}
	}


	/**
	 * @ORM\PostRemove
	 */
	public function changeMontantActuelCompteDelete(LifecycleEventArgs $args)
	{
		if (!$this->isPlanified)
		{
			$em	=	$args->getEntityManager();
			$compte = $this->getCompte();
			
			$compte->setMontantActuel($compte->getMontantActuel() - $this->montant);
			
			$em->flush();
		}
	}
	
	/**
	 * @ORM\PreUpdate
	 */
	public function memorizeOldState (PreUpdateEventArgs $args)
	{
		if ($args->hasChangedField('compte'))
		{	
			$this->oldCompte		= $args->getOldValue('compte');

		}
		if ($args->hasChangedField('montant'))
		{
			$this->oldMontant		= $args->getOldValue('montant');
		}
		if ($args->hasChangedField('isPlanified'))
		{
			$this->oldIsPlanified	= $args->getOldValue('isPlanified');
		}
		
	}
	
	/**
	 * 
	 * @ORM\PostUpdate
	 */
	public function changeMontantActuelCompteUpdate (LifecycleEventArgs $args)
	{	

		$em		= $args->getEntityManager();
		$compte	= $this->getCompte();
		
		//pas de changement de compte
		if (null === $this->oldCompte)
		{
			//suppression de l'impact du mouvement financier (s'il y a) sur le compte lorsque ce dernier avait impacté le compte 
			if (($this->oldIsPlanified===false)||	(($this->oldIsPlanified===null)&&($this->isPlanified===false)))
			{
				$compte->setMontantActuel($compte->getMontantActuel() - (($this->oldMontant === null) ? $this->montant : $this->oldMontant ));
			}
			//ajout de l'impact du mouvement financier sur le compte (s'il y a)
			if ($this->isPlanified===false)
			{
				$compte->setMontantActuel($compte->getMontantActuel() + $this->montant);
			}
		}
		//changement de compte
		else
		{
			//suppression de l'impact du mouvement financier (s'il y a) sur l'ancien compte lorsque ce dernier avait impacté le compte
			if (($this->oldIsPlanified===false)||	(($this->oldIsPlanified===null)&&($this->isPlanified===false)))
			{
				$this->oldCompte->setMontantActuel($this->oldCompte->getMontantActuel() - (($this->oldMontant === null) ? $this->montant : $this->oldMontant ));
			}
			//ajout de l'impact du mouvement financier sur le compte (s'il y a)
			if ($this->isPlanified===false)
			{
				$compte->setMontantActuel($compte->getMontantActuel() + $this->montant);
			}
			
		}
		$em->flush();
	}
}
