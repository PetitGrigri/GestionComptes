<?php

namespace FGS\GestionComptesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeCompte
 *
 * @ORM\Table(name="type_compte")
 * @ORM\Entity
 */
class TypeCompte
{
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
     * @ORM\Column(name="libelleCourt", type="string", length=255)
     */
    private $libelleCourt;

    /**
     * @var string
     *
     * @ORM\Column(name="libelleLong", type="string", length=255)
     */
    private $libelleLong;


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
     * Set libelleCourt
     *
     * @param string $libelleCourt
     * @return TypeCompte
     */
    public function setLibelleCourt($libelleCourt)
    {
        $this->libelleCourt = $libelleCourt;

        return $this;
    }

    /**
     * Get libelleCourt
     *
     * @return string 
     */
    public function getLibelleCourt()
    {
        return $this->libelleCourt;
    }

    /**
     * Set libelleLong
     *
     * @param string $libelleLong
     * @return TypeCompte
     */
    public function setLibelleLong($libelleLong)
    {
        $this->libelleLong = $libelleLong;

        return $this;
    }

    /**
     * Get libelleLong
     *
     * @return string 
     */
    public function getLibelleLong()
    {
        return $this->libelleLong;
    }

    public function __toString() {
        return "".$this->id;
    }
}
