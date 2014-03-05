KokaCrud
========

Extendable CRUD module for ZF2 using Doctrine2 and Zend\Form\Annotation

This module is intended to serve as a common base for applications, managing their entities. Additionally it may be of interest for beginners of Zend2 with Doctrine2. It provides inheritable Create, Update, Delete actions with their corresponding views, dynamic indexes of stored entities and dynamic form creation using the Doctrine AnnotationBuilder.

Current state: Initial module and routines implemented. Thank you for trying it out!

###Installation
1. Complement your composer.json with the following statements

  ```json
  ...
  "require": {
      "koka/kokacrud": "dev-master"
  },
  "repositories": [
      {
          "type": "vcs",
          "url": "https://github.com/KosmaKaczmarski/KokaCrud.git"
      }
  ],
  ...
  ```
2. Update your project using composer (https://getcomposer.org/doc/01-basic-usage.md)

3. Add `KokaCrud` module to the module section of your `config/application.config.php`

4. Copy `./vendor/koka/kokacrud/config/kokacrud.local.php.dist` to `./config/autoload/kokacrud.local.php`. Apply desired changes to fit your needs.
      
    **Caution:** The "ExampleEntity" is enabled and registered by default in your `./config/autoload/kokacrud.local.php`. You may want to remove it before updating/creating your schema. I`d suggest to replace it with your own entity classes inheriting from KokaCrud\Entity\DataRow.

  You will have to modify:
  
  ["doctrine"]["driver"]["kokacrud"]["paths"]
  
  ["doctrine"]["driver"]["orm_default"]["drivers"]
  
  and
  
  ["kokacrud"]["entities"]
  
  if you would like to use the shipped CRUD Controller with the provided entities

5. Configure the Doctrine2 `orm_default` connection or use your existing connection. You can change your `./config/autoload/kokacrud.local.php` from:

  ```php
  
   'doctrine' => array(
          'driver' => array(
              // driver config
          ),
   ),
  
  ```
  to (MySQL example)
  ```php
  
   'doctrine' => array(
          'driver' => array(
              // driver config
          ),
          'connection' => array(
              'orm_default' => array(
                  'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                  'params' => array(
                      'host' => 'localhost',
                      'user' => 'dbuser',
                      'password' => 'dbpassword',
                      'dbname' => 'dbname',
                  )
              )
          )
   ),
  ```
  in order to setup a new connection. If you are using a different Doctrine2 connection, remember to adjust the driver config.

6. Create or update your schema using Doctrine2. (http://docs.doctrine-project.org/en/2.0.x/reference/tools.html#command-overview)

### Usage

You can use this module in two different ways:

* If you have ["kokacrud"]["enable_crud_routes"] enabled, you should be able to open `yourdomain.tld/kokacrud` to see all registered entities and access the shipped, dynamic CRUD-GUI after a correct installation.

* You can inherit from KokaCrud\Controller\AbstractActionController in order to use the CRUD actions within your own controller. In order to get it working you will have to implement the getEntityClass method returning the fully qualified classname as a string. For convenience the CRUD actions will fallback to the shipped view templates if you don't implement your own, for which you can use `./vendor/koka/kokacrud/view/koka-crud/abstract-action/*.phtml`. as a starting point. 

  In addition to use your own controller inheriting from KokaCrud\Controller\AbstractActionController you will have to setup a route that will allow additional parameters to be passed without declaring them explicitly. Consider the following example when setting up the routes for your controller:

```php
'yourroute' => array(
      "type" => "Literal",
      "options" => array(
          "route" => "/yourbaseurl",
          'defaults' => array(
              '__NAMESPACE__' => 'YourNamespace\Controller',
              'controller' => 'yourcontroller',
              'action' => 'index',
          )
      ),
      'may_terminate' => true,
      'child_routes' => array(
          "crud" => array("type" => "Segment",
              "options" => array(
                  "route" => "[/:controller][/:action]",
                  'defaults' => array(
                      '__NAMESPACE__' => 'YourNamespace\Controller',
                      'controller' => 'yourcontroller',
                      'action' => 'index',
                  ),
                  'constraints' => array(
                      'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
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
          )
      ),
  ),

```

### ToDo's

- Tests
- Better documentation
- Stable implementation of decimal, time, datetime, float, object column types
- Relationships
