<?php

namespace backend\resources\{{snake_case}};

use backend\models\items\{{CamelCase}};
use backend\components\resources\crud\ReadResource;

/**
 *  {{CamelCase}}ReadResource
 *
 *  @param {{CamelCase}} $item
 */
class {{CamelCase}}ReadResource extends ReadResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';
}