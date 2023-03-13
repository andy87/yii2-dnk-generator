<?php

namespace backend\resources\{{snake_case}};

use andy87\dnk\source\base\BaseService;
use common\components\resources\crud\CreateResource;
use backend\models\forms\{{CamelCase}}Form;

/**
 *  {{CamelCase}}CreateResource
 *
 *  @property {{CamelCase}}Form $item
 */
class {{CamelCase}}CreateResource extends CreateResource
{
    /** @var string CRUD action ID */
    public const ACTION = BaseService::CREATE;

    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-create';
}