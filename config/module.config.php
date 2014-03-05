<?php

return array(
    "kokacrud" => array(
        // Set to true if you want to use the shipped CRUD controller & dynamically created routes for your entities
        "enable_crud_routes" => true,
        // the baseurl used for the dynamic CRUD controller and routing
        "crud_baseurl" => "kokacrud",
        // available query params for index views (dont change keys)
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
        // Fallback templates (if youre using your own abstracted controller without templates, the view will fallback to the shipped ones)
        "vendor_templates" => array(
            "index" => 'koka-crud/abstract-action/index',
            "edit" => 'koka-crud/abstract-action/edit',
            "delete" => 'koka-crud/abstract-action/delete',
        ),
    // Register entities you want to use with the shipped CRUD controller here (not necessary if youre using your own abstracted controller)
    ),
    // Add template fallback dir
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
        // Patch due to a autoload bug which was in conflict with zenddevelopertools profiler (dont use it)
        // 'doctrine.orm.metadata.annotation.class' => "KokaCrud\Patch\DoctrineOrmAnnotationDriver",
);