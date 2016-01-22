<?php

namespace FGS\GestionComptesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * MouvementFinancierPlanifie
 *
 * @ORM\Table(name="mouvement_financier_planifie")
 * @ORM\Entity(repositoryClass="FGS\GestionComptesBundle\Entity\MouvementFinancierPlanifieRepository")
 */
class MouvementFinancierPlanifie
{
	public function __construct()
	{
		$this->intervalValeur	= 1;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_initiale", type="date")
     */
    private $dateInitiale;

    /**
     * @var string
     *
     * @ORM\Column(name="interval_type", type="string", length=7)
     */
    private $intervalType;

    /**
     * @var integer
     *
     * @ORM\Column(name="interval_valeur", type="integer", options={"unsigned"=true})
     * @Assert\GreaterThan(value = 0, message = "Vous ne pouvez pas avoir un interval de 0 ou moins.")
     */
    private $intervalValeur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="derniere_date", type="date", nullable=true)
     */
    private $derniereDate;

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
     *
     * @return MouvementFinancierPlanifie
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
     *
     * @return MouvementFinancierPlanifie
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


    /**
     * Set dateInitiale
     *
     * @param \DateTime $dateInitiale
     *
     * @return MouvementFinancierPlanifie
     */
    public function setDateInitiale($dateInitiale)
    {
        $this->dateInitiale = $dateInitiale;

        return $this;
    }

    /**
     * Get dateInitiale
     *
     * @return \DateTime
     */
    public function getDateInitiale()
    {
        return $this->dateInitiale;
    }

    /**
     * Set intervalType
     *
     * @param string $intervalType
     *
     * @return MouvementFinancierPlanifie
     */
    public function setIntervalType($intervalType)
    {
        $this->intervalType = $intervalType;

        return $this;
    }

    /**
     * Get intervalType
     *
     * @return string
     */
    public function getIntervalType()
    {
        return $this->intervalType;
    }

    /**
     * Set intervalValeur
     *
     * @param integer $intervalValeur
     *
     * @return MouvementFinancierPlanifie
     */
    public function setIntervalValeur($intervalValeur)
    {
        $this->intervalValeur = $intervalValeur;

        return $this;
    }

    /**
     * Get intervalValeur
     *
     * @return integer
     */
    public function getIntervalValeur()
    {
        return $this->intervalValeur;
    }

    /**
     * Set derniereDate
     *
     * @param \DateTime $derniereDate
     *
     * @return MouvementFinancierPlanifie
     */
    public function setDerniereDate($derniereDate)
    {
        $this->derniereDate = $derniereDate;

        return $this;
    }

    /**
     * Get derniereDate
     *
     * @return \DateTime
     */
    public function getDerniereDate()
    {
        return $this->derniereDate;
    }
	public function getCategorieMouvementFinancier() {
		return $this->categorieMouvementFinancier;
	}
	public function setCategorieMouvementFinancier($categorieMouvementFinancier) {
		$this->categorieMouvementFinancier = $categorieMouvementFinancier;
		return $this;
	}
	public function getCompte() {
		return $this->compte;
	}
	public function setCompte($compte) {
		$this->compte = $compte;
		return $this;
	}
	
}
