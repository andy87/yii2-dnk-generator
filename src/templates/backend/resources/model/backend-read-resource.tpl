<?php

namespace backend\resources\{{snake_case}};

use andy87\dnk\source\base\BaseService;
use common\components\resources\crud\ReadResource;
use backend\models\forms\{{CamelCase}}Form;

/**
 *  {{CamelCase}}ReadResource
 *
 *  @property {{CamelCase}}Form $item
 */
class {{CamelCase}}ReadResource extends ReadResource
{
    /** @var string CRUD action ID */
    public const ACTION = BaseService::READ;

    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';
}