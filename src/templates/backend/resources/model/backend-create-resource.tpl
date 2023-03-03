<?php

namespace backend\resources\{{snake_case}};

use backend\models\forms\{{CamelCase}}Form;
use backend\components\resources\crud\CreateResource;

/**
 *  {{CamelCase}}CreateResource
 *
 *  @param {{CamelCase}}Form $item
 */
class {{CamelCase}}CreateResource extends CreateResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-create';
}