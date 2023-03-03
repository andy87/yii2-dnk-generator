<?php

namespace frontend\components\controllers;

use common\components\core\BaseService;
use {{BaseControllerClassName}} as BaseControllerClass;

/**
 * Base Controller for environment `frontend`
 */
abstract class FrontendController extends BaseControllerClass
{
    public const SERVICE = BaseService::class;
}