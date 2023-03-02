<?php

namespace backend\components\controllers;

use common\components\core\BaseService;

/**
 * Base Controller for environment `backend`
 */
abstract class BackendController extends {{BaseControllerClassName}}
{
    public const SERVICE = BaseService::class;
}