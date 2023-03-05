<?php

namespace backend\resources\{{snake_case}};

use common\components\resources\crud\ListResource;
use backend\models\forms\{{CamelCase}};

/**
 *  {{CamelCase}}ListResource
 */
class {{CamelCase}}ListResource extends ListResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-list';
}