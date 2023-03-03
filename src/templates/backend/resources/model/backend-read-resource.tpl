<?php

namespace backend\resources\{{snake_case}};

use backend\models\items\{{CamelCase}}Form;
use backend\components\resources\crud\ReadResource;

/**
 *  {{CamelCase}}ReadResource
 *
 *  @param {{CamelCase}}Form $item
 */
class {{CamelCase}}ReadResource extends ReadResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';
}