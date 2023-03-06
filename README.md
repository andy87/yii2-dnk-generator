
# DNK

Personal library on `Yii2 advanced` for generate files pack and follow pattern: `template path`, `adapter` and other. 

### Navigation.
* ### [Description](#description)
  * [Models & Forms](#description-models-forms)
  * [Services](#description-services)
  * [Controllers](#description-controllers)
  * [Resources](#description-resources)
* ### [Use](#usage-examples)
  * [generate/setup](#command-setup)
  * [generate/gen](#command-gen)
  * [generate/gii-model](#command-gii-model)
  * [generate/gii-crud](#command-gii-crud)
  * [generate/list](#command-list)
  * [generate/gen-models](#command-gen-models)
  * [generate/gen-controllers](#command-gen-controllers)
  * [generate/gen-services](#command-gen-services)
  * [generate/gen-tests](#command-gen-tests)
  * [generate/gen-backend-views](#command-gen-backend-views)
  * [generate/gen-backend-views](#command-gen-backend-views)
  * [generate/gen-frontend-views](#command-gen-frontend-views)
* ### Profit
  * [Service](#profit-services)
  * [Resource](#profit-resources)
* ### [Setup](#setup)
  * [composer](#setup-composer)
  * [config](#setup-config)


## Description

Legend: 
* S_P&M > == set `property` & `methods` in to
* Env:`all` == all environment ( common/console/backend/frontend )
* Env:`backend` == environment `backend`
* Env:`frontend` == environment `frontend`

<a name="description-models-forms"></a>
### Models & Forms.

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

<a name="description-services"></a>
### Services.

```
📄 common/components/core/BaseService.php              // S_P&M > all services on Env:`all`
|
├─📄 backend/components/services/__Model__Service.php  // S_P&M > services by part `__Model__` on Env:`backend`
|
└─📄 frontend/components/services/__Model__Service.php // S_P&M > services by part `__Model__` on Env:`frontend`
```

<a name="description-controllers"></a>
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

<a name="description-resources"></a>
### Resources.

```
📄 common/components/core/BaseResource.php                         // S_P&M > all resources on Env:`all` 
|
├─📄 backend/components/core/resources/BackendResource.php         // S_P&M > all resources on Env:`backend`
| |
| ├─📄 backend/components/resources/CrudListResource.php          // S_P&M > all resources for view `index` on Env:`backend`
| | └─📄 backend/resources/__model__/__Model__ListResource.php    // S_P&M > resources for moled `__Model__` on view `index` Env:`backend`
| |
| ├─📄 backend/components/resources/CrudReadResource.php           // S_P&M > all resources for view `view` on Env:`backend`
| | └─📄 backend/resources/__model__/__Model__ReadResource.php     // S_P&M > resources for moled `__Model__` on view `read` Env:`backend`
| |
| └─📄 backend/components/resources/CrudUpdateResource.php         // S_P&M > all resources for view `update` on Env:`backend`
|   └─📄 backend/resources/__model__/__Model__UpdateResource.php   // S_P&M > resources for moled `__Model__` on view `update` Env:`backend`
|
└─📄 frontend/components/core/resources/FrontendResource.php       // S_P&M > all resources on Env:`frontend` 
  |
  ├─📄 frontend/resources/ReadResource.php                         // S_P&M > all resources for view `view` on Env:`frontend`
  | └─📄 frontend/resources/__model__/__Model__ReadResource.php    // S_P&M > resources for moled `__Model__` on view `read` Env:`frontend`
  |
  └─📄 frontend/resources/ListResource                             // S_P&M > all resources for view `list` on `frontend` environment
    └─📄 frontend/resources/__model__/__Model__ListResource.php    // S_P&M > resources for moled `__Model__` on view `list` Env:`frontend`
```



## Usage/Examples.

<a name="command-setup"></a>
### command  

```bash
php yii generate/setup `bool`
```

| Parameter    | Type   | Description                                            |
|:-------------|:-------|:-------------------------------------------------------|
| `overwrite`  | `bool` | **optional**. overwrite exists file. defaulkt: `false` |

#### description

Generate files:
* `common/components/Part.php` - service parts with labels
* `common/components/db/Setup.php` - Data base config: CHARACTER, COLLATE, ENGINE
* `common/components/db/Tables.php` - class with table names used Part::CONSTANT_NAME
* `common/components/resources/GridViewResource.php` - GridView Resources ( search, dataProvider )
* `backend/components/controllers/BackendController.php` - Base backend controller with common methods
* `frontend/components/controllers/FrontendController.php` - Base frontend controller with common methods
* `common/components/resources/crud/CreateResource.php` - common resource for view create
* `common/components/resources/crud/ListResource.php` - common resource for view list(index)
* `common/components/resources/crud/ReadResource.php` - common resource for view read(view)
* `common/components/resources/crud/UpdateResource.php` - common resource for view update
_____


<a name="command-gen"></a>
### command  

```bash
php yii generate/gen `string` `bool`
```

| Parameter | Type     | Description                                                                       |
|:----------| :------- |:----------------------------------------------------------------------------------|
| `entity`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

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
   - backend-list-resource `backend/resources/user/UserReadResource.php` [view](src/templates/backend/resources/model/backend-list-resource.tpl)
   - backend-update-resource `backend/resources/user/UserUpdateResource.php` [view](src/templates/backend/resources/model/backend-update-resource.tpl)


 - **backend/controller**
   - backend-model-controller `backend/controller/cruds/UserController.php` [view](src/templates/backend/controllers/cruds/backend-model-controller.tpl)

 - **backend/views**
   - backend-views-form `backend/views/user/_form-user.php` [view](src/templates/backend/views/model/_form-model.tpl)
   - backend-views-create `backend/views/user/user-create.php` [view](src/templates/backend/views/model/model-create.tpl)
   - backend-views-update `backend/views/user/user-update.php` [view](src/templates/backend/views/model/model-update.tpl)
   - backend-views-read `backend/views/user/user-read.php` [view](src/templates/backend/views/model/model-read.tpl)
   - backend-views-list `backend/views/user/user-list.php` [view](src/templates/backend/views/model/model-list.tpl)


 - **frontend/models**
   - frontend-model-item `frontend/models/items/User.php` [view](src/templates/frontend/models/items/frontend-model-item.tpl)
   - frontend-model-form `frontend/models/forms/UserForm.php` [view](src/templates/frontend/models/forms/frontend-model-form.tpl)


 - **frontend/service**
   - frontend-service `frontend/service/UserService.php` [view](src/templates/frontend/services/frontend-service.tpl)


 - **frontend/resources**
   - frontend-list-resource `frontend/resources/user/UserListResource.php` [view](src/templates/frontend/resources/model/frontend-list-resource.tpl)
   - frontend-read-resource`frontend/resources/user/UserReadResource.php` [view](src/templates/frontend/resources/model/frontend-read-resource.tpl)


 - **frontend/controllers**
   - frontend-model-controller `frontend/consrollers/UserController.php` [view](src/templates/frontend/controllers/frontend-model-controller.tpl)
 
 - **frontend/views**
    - frontend-views-read `frontend/views/user/user-read.php` [view](src/templates/frontend/views/model/model-read.tpl)
    - frontend-views-list `frontend/views/user/user-list.php` [view](src/templates/frontend/views/model/model-list.tpl)
_____

<a name="command-gii-model"></a>
### command  

```bash
php yii generate/gii-model `string` `bool`
```

| Parameter | Type     | Description   |
|:----------| :------- |:--------------|
| `entity`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate gii `model` for select table(part).

\* - generate list from config `parts`

#### Examples

```bash
php yii generate/gii-model user
php yii generate/gii-model *
```

alias
```bash
php yii gii/model
```
______


<a name="command-gii-crud"></a>
### command  

```bash
php yii generate/gii-crud `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate gii `crud` for select table(part).  
\* - generate list from config `parts`

#### Examples

```bash
php yii generate/gii-crud user
php yii generate/gii-crud *
```
alias `php yii gii/crud`
______


<a name="command-list"></a>
### command  

```bash
php yii generate/list `string` `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `map`     | `string` | **Required**. list of files to generate |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate selected fileType(list fileType) for needle table(part).


#### description


##### Generate once file.

```bash
php yii generate/list `string` `string` `bool`
```

| Parameter   | Type     | Description                                                                       |
|:------------| :------- |:----------------------------------------------------------------------------------|
| `entiry`    | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `map`       | `string` | **Required**. map of file types                                                   |
| `overwrite` | `bool` | **optional**. overwrite exists file. default: `false`                             |

\* - generate list from config `parts`

generate only: `common/services/UserService.php` [view](src/templates/common/services/common-service.tpl)


##### Generate selected files.

```bash
php yii generate/list user common-services,common-model-source,frontend-service
```

generate file list:
- `common/services/UserService.php` [view](src/templates/common/services/common-service.tpl)
- `common/models/sources/UserSource.php` [view](src/templates/common/models/sources/common-model-source.tpl)
- `frontend/service/UserService.php` [view](src/templates/frontend/services/frontend-service.tpl)
______


<a name="command-gen-models"></a>
### command  

```bash
php yii generate/gen-models `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate `model` files for needle table(part).

- **common/models**
    - common-model-source `common/models/sources/UserSource.php` [view](src/templates/common/models/sources/common-model-source.tpl)
    - common-model-item `common/models/items/User.php` [view](src/templates/common/models/items/common-model-item.tpl)

- **backend/models**
    - backend-model-item `backend/models/items/User.php` [view](src/templates/backend/models/items/backend-model-item.tpl)
    - backend-model-form `backend/models/forms/UserForm.php` [view](src/templates/backend/models/forms/backend-model-form.tpl)

- **frontend/models**
    - frontend-model-item `frontend/models/items/User.php` [view](src/templates/frontend/models/items/frontend-model-item.tpl)
    - frontend-model-form `frontend/models/forms/UserForm.php` [view](src/templates/frontend/models/forms/frontend-model-form.tpl)
_____


<a name="command-gen-controllers"></a>
### command  

```bash
php yii generate/gen-controllers `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate `controller` files for needle table(part).

- **backend/controller**
    - backend-model-controller `backend/controller/cruds/UserController.php` [view](src/templates/backend/controllers/cruds/backend-model-controller.tpl)

- **frontend/controllers**
    - frontend-model-controller `frontend/consrollers/UserController.php` [view](src/templates/frontend/controllers/frontend-model-controller.tpl)
_____


<a name="command-gen-services"></a>
### command  

```bash
php yii generate/gen-services `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate `service` files for needle table(part).

- **common/services**
   - common-service `common/services/UserService.php` [view](src/templates/common/services/common-service.tpl)

- **backend/services**
   -  backend-service `backend/services/UserService.php` [view](src/templates/backend/services/backend-service.tpl)

- **frontend/service**
   - frontend-service `frontend/service/UserService.php` [view](src/templates/frontend/services/frontend-service.tpl)
_____


<a name="command-gen-tests"></a>
### command  

```bash
php yii generate/gen-tests `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate `test` files for needle table(part).

Example:
```bash
php yii generate/gen-tests user
```

- **common/test/unit/**
   - service `common/tests/unit/services/UserServiceTest.php` [view](src/templates/common/tests/unit/services/common-test-unit-service.tpl)
   - item `common/tests/unit/models/items/UserTest.php` [view](src/templates/common/tests/unit/models/items/common-test-unit-model-item.tpl)

- **backend/test/unit/**
    - service `backend/tests/unit/services/UserServiceTest.php` [view](src/templates/backend/tests/unit/services/backend-test-unit-service.tpl)
    - item `backend/tests/unit/models/items/UserTest.php` [view](src/templates/backend/tests/unit/models/items/backend-test-unit-model-item.tpl)
    - form `backend/tests/unit/models/items/UserFormTest.php` [view](src/templates/backend/tests/unit/models/forms/backend-test-unit-model-form.tpl)

- **frontend/test/unit/**
    - service `frontend/tests/unit/services/UserServiceTest.php` [view](src/templates/frontend/tests/unit/services/frontend-test-unit-service.tpl)
    - item `frontend/tests/unit/models/items/UserTest.php` [view](src/templates/frontend/tests/unit/models/items/frontend-test-unit-model-item.tpl)
    - form `frontend/tests/unit/models/items/UserFormTest.php` [view](src/templates/frontend/tests/unit/models/forms/frontend-test-unit-model-form.tpl)
_____


<a name="command-gen-backend-service"></a>
### command  

```bash
php yii generate/gen-backend-service `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate `service` files for needle table(part).

- **common/services**
   - common-service `common/services/UserService.php` [view](src/templates/common/services/common-service.tpl)

- **backend/services**
   -  backend-service `backend/services/UserService.php` [view](src/templates/backend/services/backend-service.tpl)

- **frontend/service**
   - frontend-service `frontend/service/UserService.php` [view](src/templates/frontend/services/frontend-service.tpl)
_____


<a name="command-gen-backend-views"></a>
### command  

```bash
php yii generate/gen-backend-views `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate `views` files for needle table(part).

- **backend/views**
    - backend-views-form `backend/views/user/_form-user.php` [view](src/templates/backend/views/model/_form-model.tpl)
    - backend-views-create `backend/views/user/user-create.php` [view](src/templates/backend/views/model/model-create.tpl)
    - backend-views-update `backend/views/user/user-update.php` [view](src/templates/backend/views/model/model-update.tpl)
    - backend-views-read `backend/views/user/user-read.php` [view](src/templates/backend/views/model/model-read.tpl)
    - backend-views-list `backend/views/user/user-list.php` [view](src/templates/backend/views/model/model-list.tpl)

alias `php yii generate/list user backend-views-form,backend-views-create,backend-views-update,backend-views-read,backend-views-list`
_____


<a name="command-gen-frontend-views"></a>
### command  

```bash
php yii generate/gen-frontend-views `string` `bool`
```

| Parameter | Type     | Description                            |
|:----------| :------- |:---------------------------------------|
| `entiry`  | `string` | **Required**. model/table to work with </br> * for use list fron property `parts` |
| `overwrite`  | `bool` | **optional**. overwrite exists file. default: `false` |

Generate `views` files for needle table(part).


- **frontend/views**
    - frontend-views-read `frontend/views/user/user-read.php` [view](src/templates/frontend/views/model/model-read.tpl)
    - frontend-views-list `frontend/views/user/user-list.php` [view](src/templates/frontend/views/model/model-list.tpl)

alias
```bash
php yii generate/list user frontend-views-read,frontend-views-list
```
_____



# Profit.  

<a name="profit-services"></a>
## Service profit  

### Example for model `User`

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

<a name="profit-resources"></a>
## Resource Profit  


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
_____


<a name="setup"></a>
## Yii2 setups  

### Install.

<a name="setup-composer"></a>
1. exec command 
```bash
composer require andy87/yii2-dnk-generator:dev-master
#OR
php composer.local require andy87/yii2-dnk-generator:dev-master
```

<a name="setup-config"></a>
2. Update config file:
- advanced: `@console\config\main.php`

```php
//.. other code

return [
    // .. other config
    
    'controllerMap' => [
        // .. other config
        
        'generate' => [  // new block
            'class'     => andy87\dnk\GenerateController::class
        ]
        
    ]
]
```


* `root` - directory with folders 'frontend',backend',common' ...  
* `command` - part of gii/... command  
* `parts` - table lis. for user *  in command property  
* `baseClasses` - list of based class names for generated file

3. exec command [generate/setup](#command-setup)
    
4. Update `config` for you require, by example: 
   
```php

use common\components\Entity;
use common\components\core\BaseModel;
use andy87\dnk\GenerateController;
use andy87\dnk\common\components\core\BaseController;
use andy87\dnk\common\components\core\BaseMigration;
use andy87\dnk\common\components\core\BaseResource;
use andy87\dnk\common\components\core\BaseService;

//.. other code

return [
    // .. other config

    'controllerMap' => [
        // .. other config
        
        'generate' => [  // new block
            'class'     => GenerateController::class,
            'command'   => [
                GenerateController::COMMAND_MODEL => implode(' ', [
                    '--modelClass={{CamelCase}}Source',
                    '--ns=common\\models\\sources',
                    '--tableName={{snake_case}}',
                    '--baseClass={{BaseModelClassName}}',
                    '&& yes'
                ]),
                GenerateController::COMMAND_CRUD => implode(' ', [
                    '--modelClass={{CamelCase}}',
                    '--controllerNamespace=backend\\controllers\\crud',
                    '--baseControllerClass=backend\\controllers\\cruds\\{{CamelCase}}Controller',
                    '--viewPath=@backend\\views\\cruds\\{{snake_case}}',
                    '--enableI18N=1',
                    '&& yes'
                ])
            ],
            'entityList' => array_keys(Entity::DATA),
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


5. Add `Entity`  
   Class: `common\components\Entity`, add `public const`  
```php
/** @var int */ 
public const CAMEL_CASE = 2;
```


6. Add `Tables`  
   Class: `common\components\db`, update `public const NAMES`  
```php
/** @var int */ 
public const NAMES = [
    // other code ...
    Entity::CAMEL_CASE => 'snake_case',
];
```

