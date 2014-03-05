<?php

namespace KokaCrud\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;

class EntityForm extends Form {

    protected $entityService;

    public function __construct($entityService, $name = null, $options = array()) {
        parent::__construct($name, $options);
        $this->setEntityService($entityService);

        $this->createForm();
    }

    /**
     * 
     * @return \KokaCrud\Model\EntityService
     */
    public function getEntityService() {
        return $this->entityService;
    }

    public function setEntityService($entityService) {
        $this->entityService = $entityService;
    }

    protected function createForm() {

        $entity = $this->getEntityService()->getNewEntity();
        $builder = new AnnotationBuilder($this->getEntityService()->getEntityManager());

        $form = $builder->createForm($entity);
        //var_dump($builder->getFormSpecification($entity));
        $this->setInputFilter($form->getInputFilter());

        foreach ($form->getElements() as $element) {
            $this->add($element);
            $this->modifyInputFilterOn($element);
        }

        $this->setHydrator(new DoctrineEntity($this->getEntityService()->getEntityManager()));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Save',
                'class'=>'btn btn-default'
        )));
    }

    protected function modifyInputFilterOn(Element $element) {

        switch ($element->getAttribute("type")) {

            case "checkbox": // Reset validation and filter chains in order to accept "0" as value
               
                $validatorChain = new \Zend\Validator\ValidatorChain();
                $filterChain = new \Zend\Filter\FilterChain();
                
                $this->getInputFilter()->get($element->getName())->setValidatorChain($validatorChain);
                $this->getInputFilter()->get($element->getName())->setFilterChain($filterChain);
                
                break;
            default:
                
                break;
        }
    }

}

?>
