<?php

namespace frontend\controllers;

use frontend\services\{{CamelCase}}Service;
use frontend\components\controllers\FrontendController;

/**
 * Controller for model `{{CamelCase}}` environment `frontend`
 *
 * @property {{CamelCase}}Service $servIce
 */
class {{CamelCase}}Controller extends FrontendController
{
    // const

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;
}