<?php

namespace KokaCrud\Controller;

use KokaCrud\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

class CrudController extends AbstractActionController {

    //put your code here
    public function getEntityClass() {

        if ($this->entityClass == NULL) {

            $config = $this->getConfig();
            $entitySlug = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

            if (isset($config["entities"]) && isset($config["entities"][$entitySlug])) {

                $entityClass = $config["entities"][$entitySlug]["entityClass"];

                $this->setEntityClass($entityClass);
            } else {
                throw new \Exception(sprintf("Invalid entityClass provided for %s", __METHOD__));
            }
        }
        return $this->entityClass;
    }

    public function listEntitiesAction() {

        $entities = $this->config["entities"];
        
        ksort($entities);
        
        $paginator = new Paginator(new ArrayAdapter($entities));
        $paginator
                ->setCurrentPageNumber((int) $this->params()->fromRoute('page'))
                ->setItemCountPerPage(5);
        
        
        return new ViewModel(array(
            "matchedRouteName" => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            "paginator" => $paginator,
            
        ));
    }

}

?>
