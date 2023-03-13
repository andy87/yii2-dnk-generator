<?php

namespace backend\resources\{{snake_case}};

use andy87\dnk\source\base\BaseService;
use common\components\resources\crud\ListResource;
use backend\models\forms\{{CamelCase}};

/**
 *  {{CamelCase}}ListResource
 */
class {{CamelCase}}ListResource extends ListResource
{
    /** @var string CRUD action ID */
    public const ACTION = BaseService::LIST;

    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-list';
}