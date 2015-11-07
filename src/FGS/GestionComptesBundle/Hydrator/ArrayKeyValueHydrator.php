<?php
 
namespace FGS\GestionComptesBundle\Hydrator;
 
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
 
class ArrayKeyValueHydrator extends AbstractHydrator
{
    /**
     * {@inheritdoc}
     */
    protected function hydrateAllData()
    {
        $result = [];

        while ($data = $this->_stmt->fetch(\PDO::FETCH_NUM)) {
            $this->hydrateRowData($data, $result);
        }
 
        asort($result);
 
        return $result;
    }
 
    /**
     * {@inheritdoc}
     */
    protected function hydrateRowData(array $data, array &$result)
    {
        $result[$data[0]] = $data[1];
    }
}
