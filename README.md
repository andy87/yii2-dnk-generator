
# DNK

## Extends Path

Legend: 
* S_P&M > == set `property` & `methods` in to
* Env:`all` == all environment ( common/console/backend/frontend )
* Env:`backend` == environment `backend`
* Env:`frontend` == environment `frontend`

### Models & Forms
```
📄 common/components/core/BaseModel.php            // S_P&M > all children models on Env:`all`
|
└─📄 common/models/sources/__Model__Source.php     // model generated by `gii`, S_P&M > all children models on Env:all
  |
  └─📄 common/models/items/__Model__.php           // S_P&M > models by part `__model__` on Env:`all`
    |
    ├─📄 backend/models/items/__Model__.php        // S_P&M > `model` by part `__model__` on Env:`backend`
    | └─📄 backend/models/forms/__Model__Form.php  // S_P&M > `form` on model `backend/models/items/__Model__` on Env:`backend`
    |
    └─📄 frontend/models/items/__Model__.php       // S_P&M > `model` by part `__model__` on Env:`frontend`
      └─📄 frontend/models/forms/__Model__Form.php // S_P&M > `form` on model `frontend/models/items/__Model__` on Env:`frontend`
```



### Servcice
```
📄 common/components/core/BaseService.php              // S_P&M > all services on Env:`all`
|
├─📄 backend/components/services/__Model__Service.php  // S_P&M > services by part `__Model__` on Env:`backend`
|
└─📄 frontend/components/services/__Model__Service.php // S_P&M > services by part `__Model__` on Env:`frontend`
```

### Controllers.
```
📄 common/components/core/BaseController.php                 // S_P&M > all controllers on Env:`all`
|
├─📄 backend/components/controllers/BackendController.php    // S_P&M > all controllers on Env:`backend`
| └─📄 backend/components/controllers/CrudController.php     // S_P&M > all controllers generated with `crud` on Env:`backend`
|   └─📄 backend/controllers/crud/__Model__Controller.php    // S_P&M > generated `crud` controllers for moled `__Model__` on Env:`backend`
|
└─📄 frontend/components/controllers/FrontendController.php  // S_P&M > all controllers on Env:`frontend`
  └─📄 frontend/controllers/__Model__Controller.php          // S_P&M > controllers for moled `__Model__` on Env:`frontend`
```

### Rsources.
```
📄 common/components/core/BaseResource.php                         // S_P&M > all resources on Env:`all` 
|
├─📄 backend/components/core/resources/BackendResource.php         // S_P&M > all resources on Env:`backend`
| |
| ├─📄 backend/components/resources/CrudIndexResource.php          // S_P&M > all resources for view `index` on Env:`backend`
| | └─📄 backend/resources/__model__/__Model__IndexResource.php    // S_P&M > resources for moled `__Model__` on view `index` Env:`backend`
| |
| ├─📄 backend/components/resources/CrudViewResource.php           // S_P&M > all resources for view `view` on Env:`backend`
| | └─📄 backend/resources/__model__/__Model__ViewResource.php     // S_P&M > resources for moled `__Model__` on view `view` Env:`backend`
| |
| └─📄 backend/components/resources/CrudUpdateResource.php         // S_P&M > all resources for view `update` on Env:`backend`
|   └─📄 backend/resources/__model__/__Model__UpdateResource.php   // S_P&M > resources for moled `__Model__` on view `update` Env:`backend`
|
└─📄 frontend/components/core/resources/FrontendResource.php       // S_P&M > all resources on Env:`frontend` 
  |
  ├─📄 frontend/resources/ViewResource.php                         // S_P&M > all resources for view `view` on Env:`frontend`
  | └─📄 frontend/resources/__model__/__Model__ViewResource.php    // S_P&M > resources for moled `__Model__` on view `view` Env:`frontend`
  |
  └─📄 frontend/resources/ListResource                             // S_P&M > all resources for view `list` on `frontend` environment
    └─📄 frontend/resources/__model__/__Model__ListResource.php    // S_P&M > resources for moled `__Model__` on view `list` Env:`frontend`
```

# Profit.

## Example for model `User`

### Service profit

once endpoint for creating on frontend, backend & tests
```
common/UserService::create(UserForm $userForm);

backend/controllers/UserController {
   action create {
      if ($this->request->isPost)
      {
         (new backend/UserService())->create($this->request->post());
      }
   }
}

frontend/controllers/UserController {
   action create {
      if ($this->request->isPost)
      {
         (new frontend/UserService())->create($this->request->post());
      }
   }
}

frontend/controllers/api/v1/UserController {
   action create {
      if ($this->request->isPost)
      {
         (new frontend/UserService())->create($this->request->post());
      }
   }
}


// Test for all controllers...

tests/unit/user/CreateTest {
   test{
      $userForm = new UserForm();
      (new common/UserService())->create($userForm);
   }
}
```

### Resource Profit

```
example:
frontend/resources/user/UsereViewProfileResources {
   public const TEMPLATE = 'user-view-profile';
   
   pub string $display_name;
   pub string $age;
   
   __construct($user) {
      $this->display_name = $user->name;
      $this->age = $user->age;
      // ... other code
   }
}

frontend/controllers/UserController {
   action viewProfile(int $id) {
      $user = new (UserService())->findByID($id);
      $R = new UsereViewProfileResources($user);
      
      return $R->content();
   }
}

frontend/controllers/api/v1/UserController {
   action viewProfile(int $id) {
      $user = new (UserService())->findByID($id);
      $R = new UsereViewProfileResources($user);
      
      return $R->json();
   }
}

frontend/views/user/user-view-profile:
<?php

use frontend/resources/user/UsereViewProfileResources;
 
/** @var UsereViewProfileResources $R - autocomplete and there is no possibility to make a typo */

?>

<div>Profile: <?= $R->display_name ?></div> 
<div>Age: <?= $R->age ?></div>

```



## Use 

### command
`php yii generate/setup`  

Generate all Base Classes for use `Template path` pattern.  
Method skip generate file if file exists.  


#### description

common Base classes list:
- `common\components\core\BaseController`
- `common\components\core\BaseModel`
- `common\components\core\BaseResource`
- `common\components\core\BaseService`


Interfaces list:
- `common\components\interfaces\ControllerInterface`
- `common\components\interfaces\ModelInterface`
- `common\components\interfaces\ResourcesInterface`
- `common\components\interfaces\ServiceInterface`


Helpers classes list:
- `console\components\core\BaseMigration`
- `console\components\resources\GredViewResource` - GredView Resources ( search, dataProvider )
- `common\components\db\Config` - Data base config: CHARACTER, COLLATE, ENGINE
- `common\components\Part` - service parts with labels
- `common\components\db\Tables` - class with table names used Part::CONSTANT_NAME


_____

### command  
`php yii generate/gen user`  

generate basic file pack:


#### description

 - **console/migration**
   - migration `console/migration/m000000_000000_create_table__user.php` [view](src/templates/console/migrations/migration.tpl)
  

 - **common/models**
   - common-model-source `common/models/sources/UserSource.php` [view](src/templates/common/models/sources/common-model-source.tpl) 
   - common-model-item `common/models/items/User.php` [view](src/templates/common/models/items/common-model-item.tpl) 


 - **common/services**
   - common-service `common/services/UserService.php` [view](src/templates/common/services/common-service.tpl)


 - **backend/models**
   - backend-model-item `backend/models/items/User.php` [view](src/templates/backend/models/items/backend-model-item.tpl)
   - backend-model-form `backend/models/forms/UserForm.php` [view](src/templates/backend/models/forms/backend-model-form.tpl)


 - **backend/services**
   -  backend-service `backend/services/UserService.php` [view](src/templates/backend/services/backend-service.tpl)  


 - **backend/resources**
   - backend-create-resource `backend/resources/user/UserCreateResource.php` [view](src/templates/backend/resources/model/backend-create-resource.tpl)
   - backend-list-resource `backend/resources/user/UserListResource.php` [view](src/templates/backend/resources/model/backend-list-resource.tpl)
   - backend-list-resource `backend/resources/user/UserViewResource.php` [view](src/templates/backend/resources/model/backend-list-resource.tpl)
   - backend-update-resource `backend/resources/user/UserUpdateResource.php` [view](src/templates/backend/resources/model/backend-update-resource.tpl)


 - **backend/controller**
   - backend-model-controller `backend/controller/cruds/UserController.php` [view](src/templates/backend/controllers/cruds/backend-model-controller.tpl)


 - **frontend/models**
   - frontend-model-item `frontend/models/items/User.php` [view](src/templates/frontend/models/items/frontend-model-item.tpl)
   - frontend-model-form `frontend/models/forms/UserForm.php` [view](src/templates/frontend/models/forms/frontend-model-form.tpl)


 - **frontend/service**
   - frontend-service `frontend/service/UserService.php` [view](src/templates/frontend/services/frontend-service.tpl)


 - **frontend/resources**
   - frontend-**list-reso**urce `frontend/resources/user/UserListResource.php` [view](src/templates/frontend/resources/model/frontend-list-resource.tpl)
   - frontend-view-resource`frontend/resources/user/UserViewResource.php` [view](src/templates/frontend/resources/model/frontend-view-resource.tpl)


 - **frontend/consrollers**
   - frontend-model-controller `frontend/consrollers/UserController.php` [view](src/templates/frontend/controllers/frontend-model-controller.tpl)

_____


### command
`php yii generate/list user common/services`

Generate selected files for needle table(part).


#### description


##### Generate once type file.
`php yii generate/list user common/services`

generate only: `common/services/UserService.php` [view](src/templates/common/services/common-service.tpl)


##### Example for generate some files.
`php yii generate/list user common/services,common-model-source,frontend/service`

generate file list:
   - `common/services/UserService.php` [view](src/templates/common/services/common-service.tpl)
   - `common/models/sources/UserSource.php` [view](src/templates/common/models/sources/common-model-source.tpl)
   - `frontend/service/UserService.php` [view](src/templates/frontend/services/frontend-service.tpl)

______


## Yii2 setups

### Install.

1. Add in to file `composer.json` on part `repositories`

```json
{
    "type": "git",
    "url": "https://github.com/andy87/yii2-dnk-generator.git"
}
```

2. exec command `composer require andy87/yii2-dnk-generator:dev-master`


3. Update config file:
- advanced: `@console\config\main.php`
- basic: `@app\config\console.php`


```php
//first add `use`
use andy87\dnk\GenerateController;
    
//.. other add parts
return [
    'aliases' => [
        // .. other config
        '@root'   => dirname(dirname(__DIR__)), // new line
    ],
    'controllerMap' => [
        // .. other config
        'generate' => [  // new block
            'class'     => GenerateController::class,
            'root'      => '@root', // alias to root project
            'baseClasses' => [
                GenerateController::BASE_MIGRATE_CLASS => BaseMigration::class, // extends for `Migrations`
                GenerateController::BASE_CONTROLLER_CLASS => BaseController::class, // extends for `Controllers`
                GenerateController::BASE_MODEL_CLASS => BaseModel::class, // extends for `Source` models
                GenerateController::BASE_SERVICE_CLASS => BaseService::class, // extends for `Services`
                GenerateController::BASE_RESOURCE_CLASS => BaseResource::class, // extends for `Resources`
            ]
        ]
    ]
]
```