<?php

namespace FGS\GestionComptesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use FGS\GestionComptesBundle\FGSGestionComptesBundle;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\Query;

/**
 * CompteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompteRepository extends EntityRepository
{
	public function deleteCompteById($id)
	{
		//TODO Quand la l'entity utilisateur aura été créée, il faudra penser à ajouter la condition
		$query	= $this->_em->createQuery("DELETE FGSGestionComptesBundle:Compte c WHERE c.id = :id");
		$query->setParameter("id", $id);
		
		return $query->getResult();
		
		
	}

	
	/**
	 * Permet de connaitre le nombre de mouvement financiers associé à un compte
	 * 
	 * @param int $id l'identifiant du compte
	 * @return \Doctrine\ORM\mixed
	 */
	public function getCompteMaxMouvements($id)
	{
		return $this->_em->createQuery('	SELECT 		COUNT(mf.id)
											FROM 		FGSGestionComptesBundle:Compte as c
											LEFT JOIN 	c.mouvementFinanciers as mf
											WHERE 		c.id = ?1
											')
											->setParameter('1', $id)
											->getSingleScalarResult();
		
		
	}

	public function getCompteMouvementAndCategorie($id, $ligneDepart = 1, $ligneFin = 30)
	{
		return $this->_em->createQueryBuilder()
			->select(array('c', 'mf', 'b', 'cmf'))
			->from('FGSGestionComptesBundle:Compte', 'c')
			->leftJoin('c.mouvementFinanciers', 'mf')
			->leftJoin('c.banque', 'b')
			->leftJoin('mf.categorieMouvementFinancier', 'cmf')
			->andWhere('c.id = ?1')
			->orderBy('mf.date', 'DESC')
			->setParameter('1', $id)
			->setFirstResult($ligneDepart)
			->setMaxResults($ligneFin)
			->getQuery()->getResult()
			;
	}
	
	public function getCompteMouvementAndCategorieMois($id, $date)
	{
		return $this->_em->createQueryBuilder()
			->select(array('c', 'mf', 'b', 'cmf'))
			//->addSelect('SUBSTRING(mf.date, 1, 7) as date')
			->from('FGSGestionComptesBundle:Compte', 'c')
			->leftJoin('c.mouvementFinanciers', 'mf', 'WITH', 'SUBSTRING(mf.date, 1, 7) = ?2')
			->leftJoin('c.banque', 'b')
			->leftJoin('mf.categorieMouvementFinancier', 'cmf')
			->andWhere('c.id = ?1')
			->orderBy('mf.date', 'DESC')
			->setParameter('1', $id)
			->setParameter('2', $date)
			->getQuery()->getResult();
			;
	}

	public function getMontantForEachCategorie($id, $date)
	{
		return $this->_em->createQueryBuilder()
			->select('cmf.libelle as libelle_categorie')
			->addSelect('SUM(mf.montant) as total')
			->from('FGSGestionComptesBundle:MouvementFinancier', 'mf')
			->leftJoin('mf.categorieMouvementFinancier', 'cmf')
			->leftJoin('mf.compte', 'c')
			->groupBy('cmf')
			->andWhere('c.id = ?1')
			->andWhere('SUBSTRING(mf.date, 1, 7) = ?2')
			->orderBy('mf.date', 'DESC')
			->setParameter('1', $id)
			->setParameter('2', $date)
			->addOrderBy('total', 'DESC')
			->getQuery()->getResult()
		
		;
	}
	
	public function getDepenseAndRevenu($id, $date)
	{
		return $this->_em->createQueryBuilder()
			->select('cmf.type as type')
			->addSelect('SUM(mf.montant) as total')
			->from('FGSGestionComptesBundle:MouvementFinancier', 'mf')
			->leftJoin('mf.categorieMouvementFinancier', 'cmf')
			->leftJoin('mf.compte', 'c')
			->groupBy('cmf.type')
			->andWhere('c.id = ?1')
			->andWhere('SUBSTRING(mf.date, 1, 7) = ?2')
			->orderBy('mf.date', 'DESC')
			->setParameter('1', $id)
			->setParameter('2', $date)
			->getQuery()->getResult('array_key_value_hydrator');
	}
	
	
	public function getCompteAndBanqueForUtilisateur($utilisateurId)
	{
		$conn = $this->getEntityManager()->getConnection();
		$conn->beginTransaction();
		$conn->executeQuery('SET @num:=0, @compte:=0;');
	
		$rsm	= new ResultSetMapping(array());
	
		$rsm->addEntityResult('FGS\GestionComptesBundle\Entity\Compte', 'c');
		$rsm->addFieldResult('c', 'compte_id', 		'id');
		$rsm->addFieldResult('c', 'compte_nom', 	'nom');
		$rsm->addFieldResult('c', 'compte_montant', 'montantActuel');
	
		$rsm->addJoinedEntityResult('FGS\GestionComptesBundle\Entity\Banque', 'b', 'c',  'banque');
		$rsm->addFieldResult('b', 'banque_id', 'id');
		$rsm->addFieldResult('b', 'banque_nom', 'nom');
		$rsm->addFieldResult('b', 'banque_image', 'urlImage');
	
		$rsm->addJoinedEntityResult('FGS\GestionComptesBundle\Entity\MouvementFinancier', 'mf', 'c', 'mouvementFinanciers' );
		$rsm->addFieldResult('mf', 'mf_id', 'id');
		$rsm->addFieldResult('mf', 'mf_libelle', 'libelle');
		$rsm->addFieldResult('mf', 'mf_montant', 'montant');
		$rsm->addFieldResult('mf', 'mf_commentaire', 'commentaire');
		$rsm->addFieldResult('mf', 'mf_date', 'date');
	
		$rsm->addJoinedEntityResult('FGS\GestionComptesBundle\Entity\CategorieMouvementFinancier', 'cmf', 'mf', 'categorieMouvementFinancier' );
		$rsm->addFieldResult('cmf', 'cmf_id', 'id');
		$rsm->addFieldResult('cmf', 'cmf_libelle', 'libelle');
		$rsm->addFieldResult('cmf', 'cmf_icone', 'icone');
	
		return $this->getEntityManager()
		->createNativeQuery('	SELECT 		compte.id				as compte_id,
											compte.nom				as compte_nom,
											compte.montant_actuel	as compte_montant,
									        banque.id				as banque_id,
									        banque.nom				as banque_nom,
									        banque.urlImage			as banque_image,
									        tempo_mf.id				as mf_id,
									        tempo_mf.libelle		as mf_libelle,
									        tempo_mf.montant		as mf_montant,
											tempo_mf.date			as mf_date,
											tempo_mf.commentaire	as mf_commentaire,
									        cmf.id					as cmf_id,
									        cmf.libelle				as cmf_libelle,
									        cmf.icone				as cmf_icone
									FROM 	compte
									LEFT JOIN (
										SELECT 	id,
									    		libelle,
									    		montant,
									    		compte_id,
												date,
												commentaire,
									    		categorie_mouvement_financier_id,
									            @num := if(`compte_id`=@compte, @num+1, 1) as row_number,
									            @compte := `compte_id` as var_compte
									    FROM 	mouvement_financier
										ORDER BY compte_id, date DESC, id DESC
									) AS tempo_mf
									ON compte.id	=	tempo_mf.compte_id
									LEFT JOIN banque
									ON compte.banque_id = banque.id
									LEFT JOIN categorie_mouvement_financier as cmf
									ON cmf.id = tempo_mf.categorie_mouvement_financier_id
									WHERE (tempo_mf.row_number is NULL
									OR 	tempo_mf.row_number <= 2)
									AND compte.utilisateur_id = ?', $rsm)
										->setParameter('1', $utilisateurId)
										->execute();
		//voir pourquoi, mais ma requête contient un ? et non pas un ?1 qui plantait... why ? it's the question
	}
	
	public function getComptesForUtilisateur($utilisateurId)
	{
		$qb= $this->_em->createQueryBuilder()
			->select('c')
			->from('FGSGestionComptesBundle:Compte', 'c')
			->leftJoin('c.utilisateur', 'u')
			->andWhere('u.id = ?1')
			->setParameter('1', $utilisateurId);
		
		return $qb->getQuery()->getResult();
	}
}
