<?php

return array(
    "kokacrud" => array(
        "enable_crud_routes" => true,
        "crud_baseurl" => "kokacrud",
        "crud_query_params" => array(
            "search_param_key" => "search",
            "scope_param_key" => "scope",
            "sort_param_key" => "sort",
            "order_param_key" => "order",
        ),
        // Apply template fallback only to viewmodels capturing to:
        "template_fallback_captureto" => array(
            "content"
        ),
        // Fallback templates
        "vendor_templates" => array(
            "index" => 'koka-crud/abstract-action/index',
            "edit" => 'koka-crud/abstract-action/edit',
            "delete" => 'koka-crud/abstract-action/delete',
        ),
        "entities" => array(
            "exampleentity" => array(
                "entityClass" => "KokaCrud\Entity\ExampleEntity"
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'KokaCrud\Controller\Crud' => 'KokaCrud\Controller\CrudController',
        ),
    ),
    // Add template fallback dir
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // 'doctrine.orm.metadata.annotation.class' => "KokaCrud\Patch\DoctrineOrmAnnotationDriver",
    'doctrine' => array(
        'driver' => array(
            'kokacrud_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/KokaCrud/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'KokaCrud\Entity' => 'kokacrud_entities'
                )
            )
        )
    ),
);