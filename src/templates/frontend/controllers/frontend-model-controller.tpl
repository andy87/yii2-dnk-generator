<?php

namespace frontend\controllers;

use frontend\services\{{CamelCase}}Service;
use frontend\models\search\{{CamelCase}}Search;
use frontend\components\controllers\FrontendController;

/**
 * Controller for model `{{CamelCase}}` environment `frontend`
 *
 * @property {{CamelCase}}Service $service
 */
class {{CamelCase}}Controller extends FrontendController
{
    // константы

    /** @var string Имя модели поиска */
    public const SEARCH = {{CamelCase}}Search::class;

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;
}