<?php

namespace FGS\GestionComptesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FGS\GestionComptesBundle\Model\UtilisateurInterface;

/**
 * CategorieMouvementFinancier
 *
 * @ORM\Table(name="categorie_mouvement_financier")
 * @ORM\Entity(repositoryClass="FGS\GestionComptesBundle\Entity\CategorieMouvementFinancierRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CategorieMouvementFinancier
{
	const TYPE_DEPENSE 	= 'depense';
	const TYPE_REVENU 	= 'revenu';
	
	public function __contruct()
	{
		$this->childrens	= new \Doctrine\Common\Collections\ArrayCollection();
		$this->level 		= 1;
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
     * @ORM\Column(name="icone", type="string", length=255)
     */
    private $icone;

    /**
     * @ORM\Column(name="ordre", type="integer")
     * @var unknown
     */
    private $ordre;
    
	/**
	 * @ORM\ManyToOne(targetEntity="FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier", inversedBy="childrens")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 * @var unknown
	 */
    private $parent;

    /**
     * @ORM\OneToMany(	targetEntity="FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier", 
     * 					mappedBy="parent",
     * 					cascade={"remove"} 
     * 				)
     * 
     * @var unknown
     */
    private $childrens;
    
    
    
    /**
     * @ORM\Column(name="type", type="string", length=7)
     * 
     * @var unknown
     */
    private $type;
    
    /**
     * @ORM\ManyToOne(targetEntity="FGS\GestionComptesBundle\Model\UtilisateurInterface")
     * @ORM\JoinColumn(nullable=false)
     * @var UtilisateurInterface
     */
    private $utilisateur;

    
    protected $level;
    
    public function __toString(){
    	$avant = '';
    	for ($a=0; $a<$this->level; $a++)
    	{
    		$avant.='  ';
    	}
    	
    	return $avant.$this->libelle;    
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
     * Set libelle
     *
     * @param string $libelle
     * @return CategorieMouvementFinancier
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
     * Set icone
     *
     * @param string $icone
     * @return CategorieMouvementFinancier
     */
    public function setIcone($icone)
    {
        $this->icone = $icone;

        return $this;
    }

    /**
     * Get icone
     *
     * @return string 
     */
    public function getIcone()
    {
        return $this->icone;
    }
    
    
	public function getParent() {
		return $this->parent;
	}
	
	public function setParent( CategorieMouvementFinancier $parent) {
		$this->parent = $parent;
		return $this;
	}
	
	public  function hasParent()
	{
		return  $this->parent !== null;
	}
	
	public function getChildrens() {
		return $this->childrens;
	}
	
	public function setChildrens(\Doctrine\Common\Collections\ArrayCollection $childrens) {
		$this->childrens = $childrens;
		return $this;
	}
	
	public function addChildren(CategorieMouvementFinancier $children)
	{
		$this->childrens[]=$children;
	}
	
	public  function hasChildren()
	{
		return  !$this->childrens->isEmpty();
	}
	
	
	public function getLevel() {
		return $this->level;
	}
	public function setLevel($level) {
		$this->level = $level;
		return $this;
	}

	public function getOrdre() {
		return $this->ordre;
	}
	public function setOrdre($ordre) {
		$this->ordre = $ordre;
		return $this;
	}
	

	public function getType() {
		return $this->type;
	}
	
	public function setType($type) 
	{
		if (!in_array($type, array(self::TYPE_DEPENSE, self::TYPE_REVENU))) 
		{
            throw new \InvalidArgumentException("Type invalide");
        }
        $this->type = $type;
        return $this;
	}
	
	public function getUtilisateur() {
		return $this->utilisateur;
	}
	public function setUtilisateur($utilisateur) {
		$this->utilisateur = $utilisateur;
		return $this;
	}
	
}
