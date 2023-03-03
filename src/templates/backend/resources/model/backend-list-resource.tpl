<?php

namespace backend\resources\{{snake_case}};

use backend\models\forms\{{CamelCase}};
use backend\components\resources\crud\ListResource;

/**
 *  {{CamelCase}}ListResource
 */
class {{CamelCase}}ListResource extends ListResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-list';
}