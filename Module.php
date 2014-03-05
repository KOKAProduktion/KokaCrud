<?php

namespace KokaCrud;

use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module {

    protected $config;

    /**
     * Lazy load aggregated config
     * @param MvcEvent|ServiceLocatorInterface $source
     * @return array aggregated config
     */
    protected function getAggregatedConfig($source) {
        if ($this->config == NULL) {
            if ($source instanceof MvcEvent) {
                $config = $source->getApplication()->getServiceManager()->get("Config");
            } elseif ($source instanceof ServiceLocatorInterface) {
                $config = $source->getServiceLocator()->get('Config');
            }
            $this->config = $config["kokacrud"];
        }
        return $this->config;
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getControllerConfig() {
        return array(
            'initializers' => array(
                function ($instance, $sm) {
                    //inject configuration into controllers implementing ConfigAware interface
                    if ($instance instanceof ConfigAwareInterface) {
                        $instance->setConfig($this->getAggregatedConfig($sm));
                    }
                }
            )
        );
    }

    public function getServiceConfig() {

        return array(
            'initializers' => array(
                function ($instance, $sm) {
                    //inject configuration into services implementing ConfigAware interface
                    if ($instance instanceof ConfigAwareInterface) {
                        $instance->setConfig($this->getAggregatedConfig($sm));
                    }
                }
            ),
            'invokables' => array(
                "EntityService" => "\KokaCrud\Model\EntityService"
            ),
        );
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $e) {

        $this->setupCrudRoutes($e);
        $this->attachTemplateFallbackListener($e);
    }

    protected function attachTemplateFallbackListener(MvcEvent $e) {
        $e->getApplication()
                ->getEventManager()
                // Attach before rendering starts
                ->attach(MvcEvent::EVENT_RENDER, function($e) {
                            $config = $this->getAggregatedConfig($e);
                            $resolver = $e->getApplication()->getServiceManager()->get("ViewResolver");
                            // Iterate over children assuming that the parent model is the layout
                            foreach ($e->getViewModel()->getIterator() as $viewModel) {
                                // Only apply to defined fallback captureTo`s
                                if (in_array($viewModel->captureTo(), $config["template_fallback_captureto"])) {
                                    // If desired templates don`t exist, use vendor templates
                                    if ($resolver->resolve($viewModel->getTemplate()) == false) {
                                        //extract only the action
                                        $templateArray = explode("/", $viewModel->getTemplate());
                                        $index = end($templateArray);
                                        // Switch to vendor template if one exists for the given action
                                        if (isset($config["vendor_templates"][$index])) {
                                            $viewModel->setTemplate($config["vendor_templates"][$index]);
                                        }
                                    }
                                }
                            }
                        });
    }

    protected function setupCrudRoutes(MvcEvent $e) {

        // Only create dynamic routes if running http request. Causes Fatal Error when used in cli : Zend\Mvc\Router\RoutePluginManager::get was unable to fetch or create an instance for Literal
        if ($e->getRequest() instanceof \Zend\Http\Request) {

            $config = $this->getAggregatedConfig($e);
            if (isset($config["entities"]) && $config["enable_crud_routes"]) {

                $router = $e->getApplication()->getServiceManager()->get("router");

                $routes = array();

                // Base route to list registered entities
                $routes["kokacrud"] = array(
                    "type" => "Segment",
                    "options" => array(
                        "route" => "/" . $config["crud_baseurl"] . "",
                        'defaults' => array(
                            '__NAMESPACE__' => 'KokaCrud\Controller',
                            'controller' => 'Crud',
                            'action' => 'list-entities',
                        ),
                    ),
                    'may_terminate' => false,
                    'chain_routes' => array(
                        'wildcard' => array(
                            'type' => 'Wildcard',
                            'may_terminate' => true,
                        )
                    ),
                );

                // Routing for each registered entity in the module config
                foreach ($config["entities"] as $slug => $entityConfig) {

                    $routes[$slug] = array(
                        "type" => "Segment",
                        "options" => array(
                            "route" => "/" . $config["crud_baseurl"] . "/" . $slug . "/:action",
                            'defaults' => array(
                                '__NAMESPACE__' => 'KokaCrud\Controller',
                                'controller' => 'Crud',
                                'action' => 'index',
                            ),
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                        ),
                        'may_terminate' => false,
                        'chain_routes' => array(
                            'wildcard' => array(
                                'type' => 'Wildcard',
                                'may_terminate' => true,
                            )
                        ),
                    );
                }

                $router->addRoutes($routes);
            }
        }
    }

}
