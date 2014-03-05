<?php

namespace KokaCrud\Model;

use KokaCrud\ConfigAwareInterface;
use KokaCrud\Form\EntityForm;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityService implements ConfigAwareInterface, ServiceLocatorAwareInterface {

    //put your code here
    protected $config = array();
    protected $entityClass;
    protected $serviceLocator;
    protected $primaryAlias = 'a';

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

        $this->serviceLocator = $serviceLocator;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityManager Entity manager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
    }

    public function getPrimaryAlias() {
        return $this->primaryAlias;
    }

    public function setPrimaryAlias($primaryAlias) {
        $this->primaryAlias = $primaryAlias;
    }

    public function getQueryBuilder() {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select($this->getPrimaryAlias());
        $queryBuilder->from($this->getEntityClass(), $this->getPrimaryAlias());

        return $queryBuilder;
    }

    public function getEntityClass() {
        return $this->entityClass;
    }

    public function setEntityClass($entityClass) {
        $this->entityClass = $entityClass;
    }

    public function createQuery($dql) {
        return $this->getEntityManager()->createQuery($dql);
    }

    public function createForm($entity = NULL) {

        if ($entity == NULL) {
            $entityClass = $this->getEntityClass();
            $entity = new $entityClass();
        }

        $form = new EntityForm($this);

        return $form;
    }

    public function findEntity($id) {
        return $this->getEntityManager()->find($this->getEntityClass(), $id);
    }

    public function getNewEntity() {
        $entityClass = $this->getEntityClass();
        return new $entityClass();
    }

    public function removeEntity($entity){
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }
    
    public function saveEntity($entity) {
        $this->getEntityManager()->persist($entity);
        return $this->getEntityManager()->flush();
    }

    public function getEntityProperties() {
        return $this->getEntityManager()->getClassMetadata($this->getEntityClass())->getColumnNames();
    }

    public function createQueryFromParams($params = array()) {

        $queryBuilder = $this->getQueryBuilder();
        $entityProperties = $this->getEntityProperties();

        foreach ($params as $key => $value) {

            switch ($key) {

                case $this->config["crud_query_params"]["search_param_key"]:
                    $searchTerm = $value;
                    break;
                case $this->config["crud_query_params"]["scope_param_key"]:
                    if (in_array($value, $entityProperties)) {
                        $scope = $this->getPrimaryAlias() . "." . $value;
                    }
                    break;
                case $this->config["crud_query_params"]["sort_param_key"]:
                    if (in_array($value, $entityProperties)) {
                        $sort = $this->getPrimaryAlias() . "." . $value;
                    }
                    break;
                case $this->config["crud_query_params"]["order_param_key"]:
                    if ($value == "asc" || $value == "desc") {
                        $order = $value;
                    }
                    break;
            }

            if (isset($sort)) {
                if (!isset($order)) {
                    $queryBuilder->orderBy($sort);
                } else {
                    $queryBuilder->orderBy($sort, $order);
                }
            }

            if (isset($searchTerm) && isset($scope)) {
                $queryBuilder->where($queryBuilder->expr()->like($scope, ":searchterm"));
                $queryBuilder->setParameter('searchterm', '%' . $searchTerm . '%');
            }
        }

        return $queryBuilder->getQuery();
    }

    public function cleanParams($params = array()) {
        $cleanParams = array();
        foreach ($params as $key => $value) {
            if (in_array($key, $this->config["crud_query_params"]) && (!empty($value) || $value=="0")) {
                $cleanParams[$key] = $value;
            }
        }
        return $cleanParams;
    }

}

?>
