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
    public const SEARCH_MODEL = {{CamelCase}}Search::class;

    /** @var string Совместимость с прежним именем константы. */
    public const SEARCH = self::SEARCH_MODEL;

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;

}
