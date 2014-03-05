<?php

namespace KokaCrud\Form;

use Zend\Form\Form;

class SearchForm extends Form {

    //put your code here

    protected $config = array();

    public function setConfig($config) {
        $this->config = $config;
    }

    public function __construct($config, $name = null, $options = array()) {
        parent::__construct($name, $options);

        $this->setConfig($config);

        $this->add(array(
            "name" => $this->config["crud_query_params"]["search_param_key"],
            "attributes" => array(
                'type' => "text",
                'placeholder'=>'Search'
            )
        ));

        $this->add(array(
            "name" => $this->config["crud_query_params"]["scope_param_key"],
            'type' => "Zend\Form\Element\Select"
        ));

        $this->add(array(
            "name" => "submit",
            "attributes" => array(
                'type' => "submit",
                'class'=>'btn btn-default',
                'value' => "Go"
            ),
                )
        );
        $this->add(array(
            "name" => "clear",
            "attributes" => array(
                'type' => "submit",
                'class'=>'btn btn-default',
                'value' => "Clear"
            ),
                )
        );
    }

    protected function rearrangeScopeOptionsArray($options) {

        $arrangedOptions = array();
        foreach ($options as $value) {
            $arrangedOptions[$value] = $value;
        }
        return $arrangedOptions;
    }

    public function setScopeOptions($options) {

        $this->get($this->config["crud_query_params"]["scope_param_key"])->setValueOptions(
                $this->rearrangeScopeOptionsArray($options)
        );
    }

}

?>
