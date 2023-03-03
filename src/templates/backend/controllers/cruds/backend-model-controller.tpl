<?php

namespace backend\controllers;

use common\components\core\BaseService;
use backend\services\{{CamelCase}}Service;
use backend\components\controllers\BackendController;
use backend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use backend\resources\{{snake_case}}\{{CamelCase}}ReadResource;
use backend\resources\{{snake_case}}\{{CamelCase}}CreateResource;
use backend\resources\{{snake_case}}\{{CamelCase}}UpdateResource;

/**
 * Controller for model `{{CamelCase}}` environment `backend`
 *
 * @property {{CamelCase}}Service $service
 */
class {{CamelCase}}Controller extends BackendController
{
    // const

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;

    // console command for generate default source `crud`:
    // shell_exec("./yii gii/crud --modelClass=backend\\models\\items\\{{CamelCase}}Source --controllerNamespace=backend\\controllers\\crud --baseControllerClass=backend\\controllers\\cruds\\{{CamelCase}}Controller --viewPath=@backend\\views\\cruds\\{{snake_case}} --enableI18N=1")

}