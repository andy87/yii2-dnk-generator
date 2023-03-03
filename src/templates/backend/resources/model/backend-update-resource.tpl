<?php

namespace backend\resources\{{snake_case}};

use backend\models\forms\{{CamelCase}}Form;
use backend\components\resources\crud\UpdateResource;

/**
 *  {{CamelCase}}UpdateResource
 *
 *  @param {{CamelCase}}Form $item
 */
class {{CamelCase}}UpdateResource extends UpdateResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-update';
}