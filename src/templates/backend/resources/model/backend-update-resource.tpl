<?php

namespace backend\resources\{{snake_case}};

use andy87\dnk\source\base\BaseService;
use common\components\resources\crud\UpdateResource;
use backend\models\forms\{{CamelCase}}Form;

/**
 *  {{CamelCase}}UpdateResource
 *
 *  @property {{CamelCase}}Form $item
 */
class {{CamelCase}}UpdateResource extends UpdateResource
{
    /** @var string CRUD action ID */
    public const ACTION = BaseService::UPDATE;

    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-update';
}