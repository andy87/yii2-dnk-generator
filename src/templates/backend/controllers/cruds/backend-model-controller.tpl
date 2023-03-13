<?php

namespace backend\controllers;

use common\components\Entity;
use backend\services\{{CamelCase}}Service;
use backend\models\search\{{CamelCase}}Search;
use backend\components\controllers\BackendController;

/**
 * Controller for model `{{CamelCase}}` environment `backend`
 *
 * @property {{CamelCase}}Service $service
 */
class {{CamelCase}}Controller extends BackendController
{
    // константы

    /** @var int entity ID */
    public const ENTITY = Entity::{{UPPER_CASE}};

    /** @var string Имя модели поиска */
    public const SEARCH = {{CamelCase}}Search::class;

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;

    // console command for generate default source `crud`:
    // shell_exec("./yii gii/crud --modelClass={{CamelCase}}Source --controllerNamespace=backend\\controllers\\crud --baseControllerClass=backend\\controllers\\cruds\\{{CamelCase}}Controller --viewPath=@backend\\views\\cruds\\{{snake_case}} --enableI18N=1")

}