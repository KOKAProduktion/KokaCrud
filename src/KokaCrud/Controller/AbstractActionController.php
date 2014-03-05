<?php

namespace KokaCrud\Controller;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use Zend\Form\Form;
use KokaCrud\ConfigAwareInterface;
use KokaCrud\Form\SearchForm;

abstract class AbstractActionController extends ZendAbstractActionController implements ConfigAwareInterface {

    protected $config = array();
    protected $entityClass;

    abstract public function getEntityClass();

    public function setConfig($config) {
        $this->config = $config;
    }

    public function getConfig() {
        return $this->config;
    }

    public function setEntityClass($entityClass) {
        $this->entityClass = $entityClass;
    }

    /**
     * 
     * @return \KokaCrud\Model\EntityService
     */
    protected function getEntityService($entityClass = "") {

        $entityService = $this->getServiceLocator()->get("EntityService");
        if ($entityClass == "") {
            $entityClass = $this->getEntityClass();
        } elseif (!class_exists($entityClass)) {
            return false;
        }
        $entityService->setEntityClass($entityClass);

        return $entityService;
    }

    public function indexAction() {

        $entityService = $this->getEntityService();
        $entityProperties = $entityService->getEntityProperties();
        $matchedRouteName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $urldecodedParams = $this->urldecodeParams($this->params()->fromRoute());
        $queryParams = $entityService->cleanParams($urldecodedParams);

        $query = $entityService->createQueryFromParams($queryParams);

        $paginator = new Paginator(new DoctrinePaginator(new ORMPaginator($query)));
        $paginator
                ->setCurrentPageNumber((int) $this->params()->fromRoute('page'))
                ->setItemCountPerPage(5);

        $searchForm = $this->getSearchForm($queryParams);

        $queryParams["action"] = "index";

        return new ViewModel(array(
            "config" => $this->getConfig(),
            "entityClass" => $this->getEntityClass(),
            "queryParams" => $queryParams,
            "matchedRouteName" => $matchedRouteName,
            "paginator" => $paginator,
            "searchForm" => $searchForm,
            "entityProperties" => $entityProperties,
            "deleteForm" => $this->getDeleteForm()
        ));
    }

    public function editAction() {

        $matchedRouteName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $request = $this->getRequest();
        $id = $this->params()->fromRoute("id");

        $entityService = $this->getEntityService();

        if ($id != NULL && is_numeric($id)) {
            $entity = $entityService->findEntity($id);
        } else {
            $entity = $entityService->getNewEntity();
        }

        $form = $this->getEntityService()->createForm($entity);

        $form->bind($entity);

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $entityService->saveEntity($entity);
                // Repopulate form with saved entity data
                $form->bind($entity);
            }
        }

        $form->prepare();

        return new ViewModel(array(
            "form" => $form,
            "matchedRouteName" => $matchedRouteName,
            "entityClass" => $this->getEntityClass(),
        ));
    }

    public function deleteAction() {

        $matchedRouteName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $request = $this->getRequest();
        $postData = $request->getPost();

        if (isset($postData["id"]) && is_numeric($postData["id"])) {

            $id = $postData["id"];

            $entityService = $this->getEntityService();
            $entity = $entityService->findEntity($id);
        } else {
            return $this->redirect()->toRoute($matchedRouteName, array("action" => "index"));
        }

        if ($entity == NULL) {
            return $this->redirect()->toRoute($matchedRouteName, array("action" => "index"));
        } else {

            $entityProperties = $entityService->getEntityProperties();

            $form = $this->getDeleteForm(true);

            $form->get("id")->setValue($id);

            if ($request->isPost() && isset($postData["confirm"])) {
                $form->setData($postData);
                if ($form->isValid()) {
                    $entityService->removeEntity($entity);
                    return $this->redirect()->toRoute($matchedRouteName, array("action" => "index"));
                }
            } elseif (isset($postData["cancel"])) {
                return $this->redirect()->toRoute($matchedRouteName, array("action" => "index"));
            }

            $form->prepare();
            return new ViewModel(array(
                "entity" => $entity,
                "form" => $form,
                "matchedRouteName" => $matchedRouteName,
                "entityClass" => $this->getEntityClass(),
                "entityProperties" => $entityProperties
            ));
        }
    }

    public function searchAction() {

        $params = array(
            "action" => "index",
        );
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            if (!isset($postData["clear"])) {
                $params = array_merge(
                        $params, $this->getEntityService()
                                ->cleanParams($postData)
                );
                $urlencodedParams = $this->urlencodeParams($params);
            } else {
                $urlencodedParams = $params;
            }
        }
        return $this->redirect()->toRoute($this->getEvent()->getRouteMatch()->getMatchedRouteName(), $urlencodedParams);
    }

    protected function urlencodeParams($params) {
        $urlencodedParams = array();
        foreach ($params as $key => $value) {
            $urlencodedParams[$key] = urlencode($value);
        }
        return $urlencodedParams;
    }

    protected function urldecodeParams($params) {
        $urldecodedParams = array();
        foreach ($params as $key => $value) {
            $urldecodedParams[$key] = urldecode($value);
        }
        return $urldecodedParams;
    }

    protected function getSearchForm($queryParams) {

        $matchedRouteName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $searchForm = new SearchForm($this->config);
        $searchForm->setScopeOptions($this->getEntityService()->getEntityProperties());
        $searchForm->setAttribute("action", $this->url()->fromRoute($matchedRouteName, array(
                    "action" => "search"
        )));
        $searchForm->setData($queryParams);

        return $searchForm;
    }

    protected function getDeleteForm($confirming = false) {

        $matchedRouteName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $form = new Form();

        $form->setAttribute("style", "margin:0;");
        $form->setAttribute("action", $this->url()->fromRoute($matchedRouteName, array(
                    "action" => "delete"
        )));

        $form->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                "required" => true,
            )
                )
        );
        if ($confirming) {
            $form->add(array(
                'name' => 'confirm',
                'attributes' => array(
                    'type' => 'submit',
                    'value' => 'Delete',
                    'class' => 'btn btn-default'
            )));
            $form->add(array(
                'name' => 'cancel',
                'attributes' => array(
                    'type' => 'submit',
                    'value' => 'Cancel',
                    'class' => 'btn btn-default'
            )));
        } else {
            $form->add(array(
                'name' => 'delete',
                'attributes' => array(
                    'type' => 'submit',
                    'value' => 'Delete',
                    'class' => 'btn btn-link'
            )));
        }
        $idInput = new \Zend\InputFilter\Input('id');
        $idInput->getValidatorChain()
                ->addValidator(new \Zend\Validator\NotEmpty())
                ->addValidator(new \Zend\Validator\Digits());

        $form->getInputFilter()->add($idInput);

        return $form;
    }

}

?>
