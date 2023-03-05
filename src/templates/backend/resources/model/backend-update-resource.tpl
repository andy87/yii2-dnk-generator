<?php

namespace backend\resources\{{snake_case}};

use common\components\resources\crud\UpdateResource;
use backend\models\forms\{{CamelCase}}Form;

/**
 *  {{CamelCase}}UpdateResource
 *
 *  @property {{CamelCase}}Form $item
 */
class {{CamelCase}}UpdateResource extends UpdateResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-update';
}