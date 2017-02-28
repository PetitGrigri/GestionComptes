<?php

namespace FGS\GestionComptesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FGS\GestionComptesBundle\Model\UtilisateurInterface;

/**
 * Compte
 *
 * @ORM\Table(name="compte")
 * @ORM\Entity(repositoryClass="FGS\GestionComptesBundle\Entity\CompteRepository")
 */
class Compte
{
	public function __construct()
	{
		$this->mouvementFinanciers	= new \Doctrine\Common\Collections\ArrayCollection();
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
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="montant_actuel", type="decimal", precision=20, scale=2)
     */
    private $montantActuel;

	/**
	 * @ORM\ManyToOne(targetEntity="FGS\GestionComptesBundle\Entity\Banque")
	 * @ORM\JoinColumn(nullable=true)
	 * 
	 * @var unknown
	 */
    private $banque;
    
    /**
     * @ORM\ManyToOne(targetEntity="FGS\GestionComptesBundle\Entity\TypeCompte")
     * @ORM\JoinColumn(nullable=true, name="typeCompte_id")
     * @var unknown
     */
    private $typeCompte;

    
    /**
     * @ORM\OneToMany(	targetEntity="FGS\GestionComptesBundle\Entity\MouvementFinancier", mappedBy="compte")
     * @var unknown
     */
    private $mouvementFinanciers;
    
    
    
    /**
     * @ORM\ManyToOne(targetEntity="FGS\GestionComptesBundle\Model\UtilisateurInterface")
     * @ORM\JoinColumn(nullable=false)
     * @var UtilisateurInterface
     */
    private $utilisateur;
    
    
    
    public function __toString(){

        return $this->nom. ' ('.$this->montantActuel.' €)';
    }
    
    
    
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
     * Set nom
     *
     * @param string $nom
     * @return Compte
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set montantActuel
     *
     * @param string $montantActuel
     * @return Compte
     */
    public function setMontantActuel($montantActuel)
    {
        $this->montantActuel = $montantActuel;

        return $this;
    }

    /**
     * Get montantActuel
     *
     * @return string 
     */
    public function getMontantActuel()
    {
        return $this->montantActuel;
    }

    /**
     * Set banque
     *
     * @param \FGS\GestionComptesBundleEntity\Banque $banque
     * @return Compte
     */
    public function setBanque(Banque $banque = null)
    {
        $this->banque = $banque;

        return $this;
    }

    /**
     * Get banque
     *
     * @return \FGS\GestionComptesBundleEntity\Banque 
     */
    public function getBanque()
    {
        return $this->banque;
    }

    /**
     * Set typeCompte
     *
     * @param \FGS\GestionComptesBundleEntity\TypeCompte $typeCompte
     * @return Compte
     */
    public function setTypeCompte(TypeCompte $typeCompte = null)
    {
        $this->typeCompte = $typeCompte;

        return $this;
    }

    
    /**
     * Get typeCompte
     *
     * @return \FGS\GestionComptesBundleEntity\TypeCompte 
     */
    public function getTypeCompte()
    {
        return $this->typeCompte;
    }
    
	public function getMouvementFinanciers() {
		return $this->mouvementFinanciers;
	}
	
	public function getMouvementFinanciersPlanified() 
	{
		$callbackPlanified = function(\FGS\GestionComptesBundle\Entity\MouvementFinancier $mf) {
			return !($mf->isPlanified());
		};
		return array_filter( $this->mouvementFinanciers->toArray(), $callbackPlanified);
	}
	
	public function getMouvementFinanciersNotPlanified() 
	{
		$callbackNotPlanified = function(\FGS\GestionComptesBundle\Entity\MouvementFinancier $mf) {
			return $mf->isPlanified();
		};
		return array_filter( $this->mouvementFinanciers->toArray(), $callbackNotPlanified);
	}
	
	
	public function setMouvementFinanciers($mouvementFinanciers) {
		$this->mouvementFinanciers = $mouvementFinanciers;
		return $this;
	}
	
	public function addMouvementFinancier(\FGS\GestionComptesBundle\Entity\MouvementFinancier $mf)
	{
		$this->mouvementFinanciers[] = $mf;
	}
	
	
	public function getUtilisateur() {
		return $this->utilisateur;
	}
	public function setUtilisateur($utilisateur) {
		$this->utilisateur = $utilisateur;
		return $this;
	}
	
	
}
