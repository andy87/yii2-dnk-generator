<?php

namespace frontend\controllers;

use \frontend\components\controllers\FrontendController;
use \frontend\services\{{CamelCase}}Service;

/**
 * Controller for model `{{CamelCase}}` environment `frontend`
 *
 * @propertry {{CamelCase}}Service $servcie
 */
class {{CamelCase}}Controller extends FrontendController
{
    public const SERVICE = {{CamelCase}}Service::class;
}