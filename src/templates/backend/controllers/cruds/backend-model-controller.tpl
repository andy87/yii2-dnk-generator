<?php

namespace backend\controllers;

use backend\services\{{CamelCase}}Service;
use backend\components\controllers\BackendController;

/**
 * Controller for model `{{CamelCase}}` environment `backend`
 *
 * @property {{CamelCase}}Service $service
 */
class {{CamelCase}}Controller extends BackendController
{
    // константы

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;

    // console command for generate default source `crud`:
    // shell_exec("./yii gii/crud --modelClass={{CamelCase}}Source --controllerNamespace=backend\\controllers\\crud --baseControllerClass=backend\\controllers\\cruds\\{{CamelCase}}Controller --viewPath=@backend\\views\\cruds\\{{snake_case}} --enableI18N=1")

}