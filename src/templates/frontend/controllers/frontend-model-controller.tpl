<?php

namespace frontend\controllers;

use yii\data\ActiveDataProvider;
use common\components\resources\GridViewResource;
use frontend\components\controllers\FrontendController;
use frontend\services\{{CamelCase}}Service;
use frontend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use frontend\resources\{{snake_case}}\{{CamelCase}}ReadResource;

/**
 * Controller for model `{{CamelCase}}` environment `frontend`
 *
 * @property {{CamelCase}}Service $servcie
 */
class {{CamelCase}}Controller extends FrontendController
{
    // const

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;
}