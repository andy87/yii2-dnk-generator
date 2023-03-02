<?php

namespace frontend\components\controllers;

use common\components\core\BaseService;

/**
 * Base Controller for environment `frontend`
 */
abstract class FrontendController extends {{BaseControllerClassName}}
{
    public const SERVICE = BaseService::class;
}