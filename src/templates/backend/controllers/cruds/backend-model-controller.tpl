<?php

namespace backend\controllers;

use backend\components\controllers\BackendController;
use backend\services\{{CamelCase}}Service;

/**
 * Controller for model `{{CamelCase}}` environment `backend`
 *
 * @param {{CamelCase}}Service $services
 */
class {{CamelCase}}Controller extends BackendController
{
    public const SERVICE = {{CamelCase}}Service::class;

    // call console command:
    // shell_exec("./yii gii/crud --modelClass=backend\\models\\items\\{{CamelCase}}Source --controllerNamespace=backend\\controllers\\crud --baseControllerClass=backend\\controllers\\cruds\\{{CamelCase}}Controller --viewPath=@backend\\views\\cruds\\{{snake_case}} --enableI18N=1")

}